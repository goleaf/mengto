<?php

declare(strict_types=1);

use App\Actions\BumpForumTopic;
use App\Actions\ChangeForumTopicState;
use App\Actions\DeleteTopic;
use App\Actions\RedirectForumTopic;
use App\Actions\RequestForumTopicUpdate;
use App\Actions\ReviewForumTopicUpdateRequest;
use App\Actions\SetForumTopicLegalHold;
use App\Enums\ForumTopicLifecycleEventType;
use App\Enums\ForumTopicStatus;
use App\Enums\ForumTopicUpdateRequestKind;
use App\Enums\ForumTopicUpdateRequestStatus;
use App\Livewire\Forum\ForumTopicLifecyclePanel;
use App\Models\ForumAnswer;
use App\Models\ForumCategory;
use App\Models\ForumCategoryLifecycleRule;
use App\Models\ForumEngagement;
use App\Models\ForumGroup;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Models\ForumTopicLifecycleEvent;
use App\Models\ForumTopicUpdateRequest;
use App\Models\User;
use App\Services\ForumTopicLifecycle;
use Database\Seeders\ForumCategorySeeder;
use Database\Seeders\ForumTopicLifecycleBackfillSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(ForumCategorySeeder::class);
});

function lifecycleTopicFor(User $author, array $attributes = []): ForumTopic
{
    return ForumTopic::factory()->create([
        'author_id' => $author->id,
        'author_key' => $author->actor_key,
        'author_name' => $author->name,
        ...$attributes,
    ]);
}

test('lifecycle schema provides audit retention and concurrency indexes', function () {
    expect(Schema::hasColumns('forum_topics', [
        'state_entered_at',
        'last_author_update_at',
        'last_bumped_at',
        'stale_review_requested_at',
        'outdated_at',
        'locked_at',
        'removed_at',
        'restored_at',
        'redirected_at',
        'redirect_path',
        'legal_hold_at',
        'retention_until',
    ]))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_topics',
            'forum_topics_lifecycle_state_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_topics',
            'forum_topics_lifecycle_category_activity_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_topics',
            'forum_topics_lifecycle_retention_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_topic_lifecycle_events',
            'forum_topic_lifecycle_events_topic_time_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_topic_update_requests',
            'forum_topic_update_requests_topic_status_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_topic_legal_holds',
            'forum_topic_legal_holds_topic_active_idx',
        ))->toBeTrue();
});

test('legacy states map to canonical states while remaining readable', function () {
    expect(ForumTopicStatus::Resolved->canonical())->toBe(ForumTopicStatus::Solved)
        ->and(ForumTopicStatus::PartiallyResolved->canonical())->toBe(ForumTopicStatus::PartiallySolved)
        ->and(ForumTopicStatus::Review->canonical())->toBe(ForumTopicStatus::PendingModeration)
        ->and(ForumTopicStatus::Closed->canonical())->toBe(ForumTopicStatus::Locked)
        ->and(ForumTopicStatus::Unanswered->canonical())->toBe(ForumTopicStatus::Open)
        ->and(ForumTopicStatus::publicValues())->toContain(
            ForumTopicStatus::Solved->value,
            ForumTopicStatus::Resolved->value,
        );

    foreach (['en', 'lt', 'ru'] as $locale) {
        app()->setLocale($locale);

        foreach (ForumTopicStatus::cases() as $status) {
            expect($status->label())->not->toBe("forum_topic_lifecycle.states.{$status->value}");
        }
    }
});

test('owner transitions are audited and stale versions are rejected', function () {
    $topic = lifecycleTopicFor($this->authenticatedUser);
    $originalVersion = $topic->lock_version;
    $action = app(ChangeForumTopicState::class);

    $solved = $action->handle(
        $this->authenticatedUser,
        $topic,
        ForumTopicStatus::Solved,
        'author-marked-solved',
        $originalVersion,
    );

    expect($solved->status)->toBe(ForumTopicStatus::Solved)
        ->and($solved->lock_version)->toBe($originalVersion + 1)
        ->and(ForumTopicLifecycleEvent::query()
            ->where('forum_topic_id', $topic->id)
            ->where('from_status', ForumTopicStatus::Published->value)
            ->where('to_status', ForumTopicStatus::Solved->value)
            ->where('reason_code', 'author-marked-solved')
            ->count())->toBe(1);

    expect(fn () => $action->handle(
        $this->authenticatedUser,
        $solved,
        ForumTopicStatus::Open,
        'author-reopened',
        $originalVersion,
    ))->toThrow(ValidationException::class);
});

test('publishing a draft records its first publication time', function () {
    $draft = lifecycleTopicFor($this->authenticatedUser, [
        'status' => ForumTopicStatus::Draft,
        'published_at' => null,
    ]);

    $published = app(ForumTopicLifecycle::class)->transition(
        topic: $draft,
        target: ForumTopicStatus::Published,
        actor: $this->authenticatedUser,
        reasonCode: 'author-published',
        expectedLockVersion: $draft->lock_version,
    );

    expect($published->status)->toBe(ForumTopicStatus::Published)
        ->and($published->published_at)->not->toBeNull();
});

test('removing a topic preserves its content relations and audit history', function () {
    $topic = lifecycleTopicFor($this->authenticatedUser);
    $answer = ForumAnswer::factory()->create(['topic_id' => $topic->id]);
    $engagement = ForumEngagement::factory()->create(['topic_id' => $topic->id]);
    $report = ForumReport::factory()->create(['topic_id' => $topic->id]);

    $removed = app(DeleteTopic::class)->handle($topic);

    expect($removed->status)->toBe(ForumTopicStatus::Removed)
        ->and(ForumTopic::query()->whereKey($topic->id)->exists())->toBeTrue()
        ->and(ForumAnswer::query()->whereKey($answer->id)->exists())->toBeTrue()
        ->and(ForumEngagement::query()->whereKey($engagement->id)->exists())->toBeTrue()
        ->and(ForumReport::query()->whereKey($report->id)->exists())->toBeTrue()
        ->and($removed->removed_at)->not->toBeNull()
        ->and($removed->lifecycleEvents()->where('reason_code', 'author-removed')->exists())->toBeTrue();

    $this->get(route('forum.topics.show', $removed))->assertOk();

    $this->actingAs(User::factory()->create())
        ->get(route('forum.topics.show', $removed))
        ->assertForbidden();
});

test('legal hold blocks destructive lifecycle changes and keeps private evidence encrypted', function () {
    $administrator = User::factory()->administrator()->create();
    $topic = lifecycleTopicFor($this->authenticatedUser);
    $privateReason = 'Preserve this topic while a documented retention review remains active.';
    $holdAction = app(SetForumTopicLegalHold::class);

    $hold = $holdAction->apply(
        $administrator,
        $topic,
        'retention-review',
        $privateReason,
        now()->addMonth()->toDateTimeString(),
    );

    expect($hold->private_reason)->toBe($privateReason)
        ->and($hold->getRawOriginal('private_reason'))
        ->not->toBe($privateReason)
        ->and($topic->refresh()->legal_hold_at)->not->toBeNull();

    expect(fn () => app(ChangeForumTopicState::class)->handle(
        $administrator,
        $topic->refresh(),
        ForumTopicStatus::Removed,
        'manual-removed',
        $topic->refresh()->lock_version,
    ))->toThrow(ValidationException::class);

    $released = $holdAction->release(
        $administrator,
        $topic->refresh(),
        'The authorized retention review is complete and the hold can be released.',
    );

    expect($released->released_at)->not->toBeNull()
        ->and($released->active_key)->toBeNull()
        ->and($topic->refresh()->legal_hold_at)->toBeNull();
});

test('update requests are idempotent private and author reviewed', function () {
    $topic = lifecycleTopicFor($this->authenticatedUser);
    $requester = User::factory()->create();
    $outsider = User::factory()->create();
    $requestAction = app(RequestForumTopicUpdate::class);
    $reason = 'The cited travel requirement has changed and needs a current official source.';

    $first = $requestAction->handle(
        $requester,
        $topic,
        ForumTopicUpdateRequestKind::UpdateRequest,
        $reason,
    );
    $second = $requestAction->handle(
        $requester,
        $topic,
        ForumTopicUpdateRequestKind::UpdateRequest,
        $reason,
    );

    expect($second->is($first))->toBeTrue()
        ->and(ForumTopicUpdateRequest::query()->count())->toBe(1)
        ->and($topic->refresh()->stale_review_requested_at)->not->toBeNull();

    expect(fn () => $requestAction->handle(
        $this->authenticatedUser,
        $topic,
        ForumTopicUpdateRequestKind::UpdateRequest,
        $reason,
    ))->toThrow(AuthorizationException::class);

    expect(fn () => app(ReviewForumTopicUpdateRequest::class)->handle(
        $outsider,
        $first,
        ForumTopicUpdateRequestStatus::Accepted,
        'This outsider must not decide the author request.',
        $first->lock_version,
    ))->toThrow(AuthorizationException::class);

    $reviewed = app(ReviewForumTopicUpdateRequest::class)->handle(
        $this->authenticatedUser,
        $first,
        ForumTopicUpdateRequestStatus::Accepted,
        'The author will update the official source and jurisdiction context.',
        $first->lock_version,
    );

    expect($reviewed->status)->toBe(ForumTopicUpdateRequestStatus::Accepted)
        ->and($reviewed->reviewed_by_user_id)->toBe($this->authenticatedUser->id);
});

test('controlled bumping enforces the category cooldown', function () {
    $category = ForumCategory::factory()->create();
    ForumCategoryLifecycleRule::factory()->create([
        'forum_category_id' => $category->id,
        'bump_cooldown_hours' => 168,
    ]);
    $topic = lifecycleTopicFor($this->authenticatedUser, [
        'forum_category_id' => $category->id,
        'last_bumped_at' => null,
    ]);
    $action = app(BumpForumTopic::class);
    $bumped = $action->handle(
        $this->authenticatedUser,
        $topic,
        $topic->lock_version,
    );

    expect($bumped->last_bumped_at)->not->toBeNull()
        ->and($bumped->lifecycleEvents()
            ->where('event_type', ForumTopicLifecycleEventType::Bumped->value)
            ->count())->toBe(1);

    expect(fn () => $action->handle(
        $this->authenticatedUser,
        $bumped,
        $bumped->lock_version,
    ))->toThrow(ValidationException::class);
});

test('redirect preserves the old route and authorizes the destination', function () {
    $administrator = User::factory()->administrator()->create();
    $source = ForumTopic::factory()->create(['slug' => 'old-canonical-topic']);
    $target = ForumTopic::factory()->create(['slug' => 'new-canonical-topic']);

    $redirected = app(RedirectForumTopic::class)->handle(
        $administrator,
        $source,
        $target,
        ForumTopicStatus::Redirected,
        'moderator-redirected',
        $source->lock_version,
    );

    expect($redirected->status)->toBe(ForumTopicStatus::Redirected)
        ->and($redirected->merged_into_topic_id)->toBe($target->id)
        ->and($redirected->redirect_path)->toBe([$target->id]);

    $this->get(route('forum.topics.show', $redirected))
        ->assertRedirect(route('forum.topics.show', $target))
        ->assertStatus(301);
});

test('topic authors do not bypass private group membership', function () {
    $groupOwner = User::factory()->create();
    $outsiderAuthor = User::factory()->create();
    $group = ForumGroup::factory()
        ->for($groupOwner, 'owner')
        ->private()
        ->create();
    $topic = ForumTopic::factory()->forGroup($group)->create([
        'author_id' => $outsiderAuthor->id,
        'author_key' => $outsiderAuthor->actor_key,
    ]);

    $this->actingAs($outsiderAuthor)
        ->get(route('forum.topics.show', $topic))
        ->assertForbidden();

    Livewire::actingAs($outsiderAuthor)
        ->test(ForumTopicLifecyclePanel::class, ['topicId' => $topic->id])
        ->assertForbidden();
});

test('livewire lifecycle panel protects identifiers and direct actions', function () {
    $owner = $this->authenticatedUser;
    $topic = lifecycleTopicFor($owner, [
        'last_author_update_at' => now()->subYear(),
        'last_activity_at' => now()->subYear(),
    ]);
    $otherTopic = ForumTopic::factory()->create();
    $outsider = User::factory()->create();

    $component = Livewire::actingAs($owner)
        ->test(ForumTopicLifecyclePanel::class, ['topicId' => $topic->id])
        ->assertSee(__('forum_topic_lifecycle.panel.stale_heading'))
        ->call('changeState', ForumTopicStatus::Solved->value)
        ->assertHasNoErrors()
        ->assertSee(__('forum_topic_lifecycle.feedback.state_changed'))
        ->assertSee(__('forum_topic_lifecycle.panel.history'))
        ->assertSee(__('forum_topic_lifecycle.reasons.manual-solved'));

    expect(fn () => $component->set('topicId', $otherTopic->id))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    Livewire::actingAs($outsider)
        ->test(ForumTopicLifecyclePanel::class, ['topicId' => $topic->id])
        ->call('changeState', ForumTopicStatus::Removed->value)
        ->assertForbidden();
});

test('production lifecycle backfill is repeatable and preserves category rules', function () {
    $category = ForumCategory::factory()->create();
    $topic = ForumTopic::factory()->create([
        'forum_category_id' => $category->id,
        'state_entered_at' => null,
        'last_author_update_at' => null,
        'retention_until' => null,
    ]);

    $this->seed(ForumTopicLifecycleBackfillSeeder::class);
    $firstRuleId = ForumCategoryLifecycleRule::query()
        ->where('forum_category_id', $category->id)
        ->value('id');
    $firstEventId = ForumTopicLifecycleEvent::query()
        ->where('idempotency_key', "topic-lifecycle-backfill:{$topic->id}")
        ->value('id');

    ForumCategoryLifecycleRule::query()
        ->whereKey($firstRuleId)
        ->update(['allow_bumping' => false, 'is_system_managed' => false]);
    $this->seed(ForumTopicLifecycleBackfillSeeder::class);

    expect($topic->refresh()->state_entered_at)->not->toBeNull()
        ->and($topic->last_author_update_at)->not->toBeNull()
        ->and($topic->retention_until)->not->toBeNull()
        ->and(ForumCategoryLifecycleRule::query()
            ->where('forum_category_id', $category->id)
            ->value('id'))->toBe($firstRuleId)
        ->and(ForumCategoryLifecycleRule::query()
            ->whereKey($firstRuleId)
            ->value('allow_bumping'))->toBeFalse()
        ->and(ForumTopicLifecycleEvent::query()
            ->where('idempotency_key', "topic-lifecycle-backfill:{$topic->id}")
            ->value('id'))->toBe($firstEventId)
        ->and(ForumTopicLifecycleEvent::query()
            ->where('idempotency_key', "topic-lifecycle-backfill:{$topic->id}")
            ->count())->toBe(1);
});

test('lifecycle events are append only', function () {
    $event = ForumTopicLifecycleEvent::factory()->create();

    expect(fn () => $event->update(['reason_code' => 'rewritten']))
        ->toThrow(LogicException::class)
        ->and(fn () => $event->delete())
        ->toThrow(LogicException::class);
});
