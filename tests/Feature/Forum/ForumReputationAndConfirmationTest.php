<?php

declare(strict_types=1);

use App\Actions\CastForumConfirmationVote;
use App\Actions\ChangeForumTrustLevel;
use App\Actions\CreateForumConfirmation;
use App\Actions\RecordReputationEvent;
use App\Actions\ReverseReputationEvent;
use App\Data\ReputationEventData;
use App\Enums\ConfirmationState;
use App\Enums\ReputationEventStatus;
use App\Models\ForumCategory;
use App\Models\ForumConfirmationVote;
use App\Models\ForumReputationAggregate;
use App\Models\ForumReputationDimension;
use App\Models\ForumReputationEvent;
use App\Models\ForumTopic;
use App\Models\ForumTrustHistory;
use App\Models\ForumTrustLevel;
use App\Models\ForumUserTrustLevel;
use App\Models\User;
use Database\Seeders\ForumSystemSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(ForumSystemSeeder::class);
});

test('reputation ledger is idempotent scoped and reversible', function () {
    $actor = User::factory()->create();
    $recipient = User::factory()->create();
    $category = ForumCategory::query()->where('stable_key', 'forum.behavior')->firstOrFail();
    $data = new ReputationEventData(
        recipient: $recipient,
        dimension: 'helpfulness',
        eventType: 'verified-help',
        sourceEntityType: 'forum-answer',
        sourceEntityId: '42',
        amount: 3,
        reasonCode: 'verified-help',
        explanationTranslationKey: 'forum_reputation.events.helpful_vote',
        idempotencyKey: 'verified-help:42',
        actor: $actor,
        forumCategoryId: $category->id,
    );
    $record = app(RecordReputationEvent::class);

    $event = $record->handle($data);
    $duplicate = $record->handle($data);

    expect($duplicate->id)->toBe($event->id)
        ->and(ForumReputationEvent::query()->count())->toBe(1)
        ->and(ForumReputationAggregate::query()->count())->toBe(2)
        ->and(ForumReputationAggregate::query()->sum('total'))->toBe(6);

    $reversal = app(ReverseReputationEvent::class)->handle(
        $event,
        'content-invalidated',
        $actor,
    );
    $sameReversal = app(ReverseReputationEvent::class)->handle(
        $event,
        'content-invalidated',
        $actor,
    );

    expect($reversal->id)->toBe($sameReversal->id)
        ->and($event->refresh()->status)->toBe(ReputationEventStatus::Reversed)
        ->and(ForumReputationEvent::query()->count())->toBe(2)
        ->and(ForumReputationAggregate::query()->sum('total'))->toBe(0);
});

test('self awards and actor recipient caps are enforced', function () {
    $actor = User::factory()->create();
    $recipient = User::factory()->create();
    $dimension = ForumReputationDimension::query()
        ->where('stable_key', 'helpfulness')
        ->firstOrFail();
    $dimension->update([
        'daily_actor_recipient_cap' => 1,
        'relationship_cap' => 1,
    ]);
    $record = app(RecordReputationEvent::class);

    $first = fn (User $target, string $key) => $record->handle(new ReputationEventData(
        recipient: $target,
        dimension: 'helpfulness',
        eventType: 'helpful-answer-vote',
        sourceEntityType: 'forum-answer',
        sourceEntityId: $key,
        amount: 1,
        reasonCode: 'helpful-vote',
        explanationTranslationKey: 'forum_reputation.events.helpful_vote',
        idempotencyKey: $key,
        actor: $actor,
    ));

    expect(fn () => $first($actor, 'self-award'))
        ->toThrow(ValidationException::class);

    $first($recipient, 'first-award');

    expect(fn () => $first($recipient, 'second-award'))
        ->toThrow(ValidationException::class)
        ->and(ForumReputationEvent::query()->count())->toBe(1);
});

test('trust levels are independent from karma and every change is audited', function () {
    $administrator = User::factory()->administrator()->create();
    $member = User::factory()->create();
    $ordinaryActor = User::factory()->create();
    $level = ForumTrustLevel::query()
        ->where('stable_key', 'community-reviewer')
        ->firstOrFail();
    $action = app(ChangeForumTrustLevel::class);

    expect(fn () => $action->handle(
        $ordinaryActor,
        $member,
        $level,
        'category',
        'forum.behavior',
        'reviewed-contributions',
    ))->toThrow(AuthorizationException::class);

    $assignment = $action->handle(
        $administrator,
        $member,
        $level,
        'category',
        'forum.behavior',
        'reviewed-contributions',
        ['case' => 'review-42'],
    );
    $sameAssignment = $action->handle(
        $administrator,
        $member,
        $level,
        'category',
        'forum.behavior',
        'reviewed-contributions',
    );

    expect($assignment->id)->toBe($sameAssignment->id)
        ->and(ForumUserTrustLevel::query()->count())->toBe(1)
        ->and(ForumTrustHistory::query()->count())->toBe(1)
        ->and(ForumReputationEvent::query()->count())->toBe(0)
        ->and($level->is_professional)->toBeFalse();
});

test('independent eligible reviewers can confirm a low risk claim', function () {
    $topic = ForumTopic::factory()->create();
    $confirmation = app(CreateForumConfirmation::class)->handle(
        $this->authenticatedUser,
        'forum-topic',
        $topic->id,
        'low',
        'The local shelter opens at nine.',
        requiredQuorum: 3,
        requiredDiversity: 2,
    );
    $reviewers = User::factory()->count(3)->create([
        'created_at' => now()->subMonth(),
    ]);
    $vote = app(CastForumConfirmationVote::class);

    $vote->handle($reviewers[0], $confirmation, 'support', 'cluster-a');
    $vote->handle($reviewers[1], $confirmation, 'support', 'cluster-b');
    $vote->handle($reviewers[2], $confirmation, 'support', 'cluster-c');

    expect($confirmation->refresh()->state)->toBe(ConfirmationState::CommunityConfirmed)
        ->and($confirmation->supporting_votes)->toBe(3)
        ->and((float) $confirmation->confidence)->toBe(1.0);

    expect(fn () => $vote->handle(
        $reviewers[0],
        $confirmation,
        'support',
        'cluster-a',
    ))->toThrow(ValidationException::class);
});

test('conflicted reviews are audited but excluded from quorum', function () {
    $confirmation = app(CreateForumConfirmation::class)->handle(
        $this->authenticatedUser,
        'forum-topic',
        ForumTopic::factory()->create()->id,
        'low',
        'A service is currently available.',
        requiredQuorum: 2,
        requiredDiversity: 2,
    );
    $reviewers = User::factory()->count(2)->create([
        'created_at' => now()->subMonth(),
    ]);
    $vote = app(CastForumConfirmationVote::class);

    $excluded = $vote->handle(
        $reviewers[0],
        $confirmation,
        'support',
        'same-organization',
        hasConflict: true,
        conflictType: 'employer',
    );
    $vote->handle($reviewers[1], $confirmation, 'support', 'independent');

    expect($excluded->status)->toBe('excluded-conflict')
        ->and(ForumConfirmationVote::query()->count())->toBe(2)
        ->and($confirmation->refresh()->supporting_votes)->toBe(1)
        ->and($confirmation->state)->toBe(ConfirmationState::CommunitySupported);
});

test('medical and legal claims cannot become community confirmed', function (string $riskClass) {
    $confirmation = app(CreateForumConfirmation::class)->handle(
        $this->authenticatedUser,
        'forum-topic',
        ForumTopic::factory()->create()->id,
        $riskClass,
        'A high-risk claim that requires professional review.',
        requiredQuorum: 2,
        requiredDiversity: 2,
    );
    $reviewers = User::factory()->count(2)->create([
        'created_at' => now()->subMonth(),
    ]);
    $vote = app(CastForumConfirmationVote::class);

    $vote->handle($reviewers[0], $confirmation, 'support', 'cluster-a');
    $vote->handle($reviewers[1], $confirmation, 'support', 'cluster-b');

    expect($confirmation->refresh()->state)
        ->toBe(ConfirmationState::CommunitySupported)
        ->not->toBe(ConfirmationState::CommunityConfirmed);
})->with(['medical', 'legal', 'public-health']);

test('requesters and new untrusted accounts cannot vote on confirmations', function () {
    $confirmation = app(CreateForumConfirmation::class)->handle(
        $this->authenticatedUser,
        'forum-topic',
        ForumTopic::factory()->create()->id,
        'low',
        'A low-risk claim.',
    );
    $newUser = User::factory()->create();
    $vote = app(CastForumConfirmationVote::class);

    expect(fn () => $vote->handle(
        $this->authenticatedUser,
        $confirmation,
        'support',
        'requester',
    ))->toThrow(AuthorizationException::class)
        ->and(fn () => $vote->handle(
            $newUser,
            $confirmation,
            'support',
            'new-account',
        ))->toThrow(AuthorizationException::class);
});
