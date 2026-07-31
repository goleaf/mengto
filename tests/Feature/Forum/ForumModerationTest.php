<?php

declare(strict_types=1);

use App\Actions\ApplyForumModerationAction;
use App\Actions\AssignForumModerationCase;
use App\Actions\OpenForumModerationCase;
use App\Actions\RecuseForumModerator;
use App\Actions\ReviewForumModerationAppeal;
use App\Actions\SubmitForumModerationAppeal;
use App\Actions\SubmitForumReport;
use App\Models\ForumBlock;
use App\Models\ForumModerationAction;
use App\Models\ForumModerationActionDefinition;
use App\Models\ForumModerationAppeal;
use App\Models\ForumModerationCase;
use App\Models\ForumReportEvent;
use App\Models\ForumReportReason;
use App\Models\ForumTopic;
use App\Models\User;
use App\Services\ForumModerationActionCatalog;
use App\Services\ForumReportReasonCatalog;
use Database\Seeders\ForumModerationDefinitionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(ForumModerationDefinitionSeeder::class);
});

test('all report reasons and moderation actions are seeded idempotently', function () {
    $reasonIds = ForumReportReason::query()->pluck('id', 'stable_key');
    $actionIds = ForumModerationActionDefinition::query()->pluck('id', 'stable_key');

    $this->seed(ForumModerationDefinitionSeeder::class);

    expect(ForumReportReason::query()->count())
        ->toBe(count(ForumReportReasonCatalog::KEYS))
        ->and(ForumModerationActionDefinition::query()->count())
        ->toBe(count(ForumModerationActionCatalog::KEYS))
        ->and(ForumReportReason::query()->pluck('id', 'stable_key')->all())
        ->toBe($reasonIds->all())
        ->and(ForumModerationActionDefinition::query()->pluck('id', 'stable_key')->all())
        ->toBe($actionIds->all());
});

test('critical reports preserve reporter privacy and do not auto convict', function () {
    $author = User::factory()->create();
    $topic = ForumTopic::factory()->create([
        'author_id' => $author->id,
        'author_key' => $author->actor_key,
    ]);
    $report = app(SubmitForumReport::class)->handle(
        reporter: $this->authenticatedUser,
        subject: $topic,
        reasonKey: 'animal-cruelty',
        details: 'The content appears to show an immediate and credible welfare risk.',
        truthfulnessConfirmed: true,
        immediateSafety: true,
    );

    expect($report)
        ->priority->toBe('critical')
        ->status->toBe('received')
        ->reporter_id->toBe($this->authenticatedUser->id)
        ->and(ForumReportEvent::query()->count())->toBe(1)
        ->and(ForumModerationCase::query()->count())->toBe(0)
        ->and($report->toArray())->not->toHaveKeys([
            'reporter_id',
            'reporter_key',
            'details',
        ]);

    expect($this->authenticatedUser->can('view', $report))->toBeTrue()
        ->and(User::factory()->create()->can('view', $report))->toBeFalse()
        ->and(User::factory()->administrator()->create()->can('view', $report))->toBeTrue();
});

test('reporting can block the affected user without notifying them', function () {
    $author = User::factory()->create();
    $topic = ForumTopic::factory()->create([
        'author_id' => $author->id,
        'author_key' => $author->actor_key,
    ]);

    app(SubmitForumReport::class)->handle(
        reporter: $this->authenticatedUser,
        subject: $topic,
        reasonKey: 'harassment',
        details: 'Repeated unwanted targeted contact after a clear request to stop.',
        truthfulnessConfirmed: true,
        blockAffectedUser: true,
    );

    expect(ForumBlock::query()
        ->where('user_key', $this->authenticatedUser->actor_key)
        ->where('blocked_author_key', $author->actor_key)
        ->exists())->toBeTrue();
});

test('reports require truthfulness and valid safety escalation', function () {
    $topic = ForumTopic::factory()->create();
    $submit = app(SubmitForumReport::class);

    expect(fn () => $submit->handle(
        $this->authenticatedUser,
        $topic,
        'spam',
        null,
        false,
    ))->toThrow(ValidationException::class)
        ->and(fn () => $submit->handle(
            $this->authenticatedUser,
            $topic,
            'spam',
            null,
            true,
            true,
        ))->toThrow(ValidationException::class);
});

test('moderation case opening is authorized audited and idempotent', function () {
    $report = app(SubmitForumReport::class)->handle(
        $this->authenticatedUser,
        ForumTopic::factory()->create(),
        'misinformation',
        'The claim cites a source that says the opposite.',
        true,
    );
    $ordinaryUser = User::factory()->create();
    $administrator = User::factory()->administrator()->create();
    $open = app(OpenForumModerationCase::class);

    expect(fn () => $open->handle($ordinaryUser, $report))
        ->toThrow(AuthorizationException::class);

    $case = $open->handle($administrator, $report);
    $sameCase = $open->handle($administrator, $report);

    expect($sameCase->id)->toBe($case->id)
        ->and($report->refresh()->status)->toBe('awaiting-review')
        ->and($case->reports()->count())->toBe(1)
        ->and(ForumReportEvent::query()->count())->toBe(2)
        ->and($case->metadata['automatic_conviction'])->toBeFalse();
});

test('a recused moderator cannot apply an action', function () {
    $administrator = User::factory()->administrator()->create();
    $report = app(SubmitForumReport::class)->handle(
        $this->authenticatedUser,
        ForumTopic::factory()->create(),
        'moderator-conflict',
        'The moderator is directly involved in the public dispute.',
        true,
    );
    $case = app(OpenForumModerationCase::class)->handle($administrator, $report);
    app(RecuseForumModerator::class)->handle(
        $administrator,
        $case,
        'personally-involved',
    );
    $definition = ForumModerationActionDefinition::query()
        ->where('stable_key', 'warning')
        ->firstOrFail();

    expect(fn () => app(ApplyForumModerationAction::class)->handle(
        $administrator,
        $case,
        $definition,
        'moderation-conflict-001',
        'community-rules',
        'forum_moderation.messages.action_applied',
        'A recused moderator must not decide this case.',
    ))->toThrow(AuthorizationException::class);
});

test('case assignment synchronizes linked reports and records reassignment history', function () {
    $opener = User::factory()->administrator()->create();
    $firstAssignee = User::factory()->administrator()->create();
    $secondAssignee = User::factory()->administrator()->create();
    $report = app(SubmitForumReport::class)->handle(
        $this->authenticatedUser,
        ForumTopic::factory()->create(),
        'wrong-category',
        'This discussion belongs in a different safety-reviewed category.',
        true,
    );
    $case = app(OpenForumModerationCase::class)->handle($opener, $report);
    $assign = app(AssignForumModerationCase::class);

    $assign->handle($opener, $case, $firstAssignee);
    $assign->handle($opener, $case->refresh(), $secondAssignee);

    expect($case->refresh())
        ->assigned_to_user_id->toBe($secondAssignee->id)
        ->status->toBe('assigned')
        ->and($report->refresh()->status)->toBe('assigned')
        ->and(ForumReportEvent::query()
            ->where('forum_report_id', $report->id)
            ->where('event_type', 'case-assigned')
            ->exists())->toBeTrue()
        ->and(ForumReportEvent::query()
            ->where('forum_report_id', $report->id)
            ->where('event_type', 'case-reassigned')
            ->where('metadata->previous_assignee_user_id', $firstAssignee->id)
            ->where('metadata->assignee_user_id', $secondAssignee->id)
            ->exists())->toBeTrue();
});

test('recusal unassigns the moderator and appends a privacy-safe report event', function () {
    $moderator = User::factory()->administrator()->create();
    $report = app(SubmitForumReport::class)->handle(
        $this->authenticatedUser,
        ForumTopic::factory()->create(),
        'moderator-conflict',
        'The assigned moderator has a documented conflict with this case.',
        true,
    );
    $case = app(OpenForumModerationCase::class)->handle($moderator, $report);
    app(AssignForumModerationCase::class)->handle($moderator, $case, $moderator);
    $recuse = app(RecuseForumModerator::class);

    expect(fn () => $recuse->handle(
        $moderator,
        $case->refresh(),
        'unsupported-conflict',
    ))->toThrow(ValidationException::class);

    $recuse->handle(
        $moderator,
        $case->refresh(),
        'prior-public-dispute',
        'Private conflict details that must not be exposed in the report event.',
    );

    $event = ForumReportEvent::query()
        ->where('forum_report_id', $report->id)
        ->where('event_type', 'moderator-recused')
        ->firstOrFail();

    expect($case->refresh())
        ->assigned_to_user_id->toBeNull()
        ->status->toBe('awaiting-review')
        ->and($event->metadata)->toBe(['reason_code' => 'prior-public-dispute'])
        ->and($event->toArray())->not->toHaveKey('internal_note');
});

test('temporary and permanent actions enforce human review safeguards', function () {
    $actor = User::factory()->administrator()->create();
    $approver = User::factory()->administrator()->create();
    $target = User::factory()->create();
    $report = app(SubmitForumReport::class)->handle(
        $this->authenticatedUser,
        ForumTopic::factory()->create(),
        'threats',
        'A credible threat requires an authorized human review.',
        true,
        true,
    );
    $case = app(OpenForumModerationCase::class)->handle($actor, $report);
    $temporary = ForumModerationActionDefinition::query()
        ->where('stable_key', 'temporary-suspension')
        ->firstOrFail();
    $permanent = ForumModerationActionDefinition::query()
        ->where('stable_key', 'permanent-suspension')
        ->firstOrFail();
    $apply = app(ApplyForumModerationAction::class);

    expect(fn () => $apply->handle(
        $actor,
        $case,
        $temporary,
        'safety-001',
        'community-safety',
        'forum_moderation.messages.action_applied',
        'Temporary protection while the evidence is reviewed.',
        $target,
    ))->toThrow(ValidationException::class)
        ->and(fn () => $apply->handle(
            $actor,
            $case,
            $permanent,
            'safety-002',
            'community-safety',
            'forum_moderation.messages.action_applied',
            'Permanent actions require a second authorized human.',
            $target,
        ))->toThrow(ValidationException::class);

    $action = $apply->handle(
        $actor,
        $case,
        $permanent,
        'safety-002',
        'community-safety',
        'forum_moderation.messages.action_applied',
        'Two authorized reviewers confirmed the evidence and policy basis.',
        $target,
        $approver,
        null,
        ['source' => 'moderator-reviewed'],
    );

    expect($action)
        ->forum_moderation_case_id->toBe($case->id)
        ->forum_moderation_action_definition_id->toBe($permanent->id)
        ->actor_user_id->toBe($actor->id)
        ->target_user_id->toBe($target->id)
        ->rule_id->toBe('safety-002')
        ->policy_basis->toBe('community-safety')
        ->scope_type->toBe('global')
        ->scope_key->toBe('global')
        ->user_reason_translation_key->toBe('forum_moderation.messages.action_applied')
        ->internal_reason->toBe('Two authorized reviewers confirmed the evidence and policy basis.')
        ->evidence->toBe(['source' => 'moderator-reviewed'])
        ->starts_at->not->toBeNull()
        ->ends_at->toBeNull()
        ->review_at->toBeNull()
        ->appeal_available->toBeTrue()
        ->reversal_of_action_id->toBeNull()
        ->reversed_at->toBeNull()
        ->and($action->metadata['senior_approver_id'])->toBe($approver->id)
        ->and($target->refresh()->status->value)->toBe('active')
        ->and($case->refresh()->status)->toBe('actioned')
        ->and($report->refresh()->status)->toBe('actioned')
        ->and(ForumReportEvent::query()
            ->where('forum_report_id', $report->id)
            ->where('event_type', 'moderation-action-recorded')
            ->exists())->toBeTrue();
});

test('appeals cannot be solely reviewed by the original moderator', function () {
    $originalModerator = User::factory()->administrator()->create();
    $appealReviewer = User::factory()->administrator()->create();
    $target = User::factory()->create();
    $case = ForumModerationCase::factory()->create([
        'opened_by_user_id' => $originalModerator->id,
    ]);
    $definition = ForumModerationActionDefinition::query()
        ->where('stable_key', 'warning')
        ->firstOrFail();
    $action = ForumModerationAction::factory()->create([
        'forum_moderation_case_id' => $case->id,
        'forum_moderation_action_definition_id' => $definition->id,
        'actor_user_id' => $originalModerator->id,
        'target_user_id' => $target->id,
        'appeal_available' => true,
    ]);
    $appeal = app(SubmitForumModerationAppeal::class)->handle(
        $target,
        $action,
        'The action relies on evidence that belongs to a different account.',
    );
    $review = app(ReviewForumModerationAppeal::class);

    expect(fn () => $review->handle(
        $originalModerator,
        $appeal,
        'upheld',
        'The original moderator cannot be the sole appeal reviewer.',
    ))->toThrow(AuthorizationException::class);

    $result = $review->handle(
        $appealReviewer,
        $appeal,
        'reversed',
        'Independent review found that the evidence identified another account.',
    );

    expect($result->status)->toBe('reversed')
        ->and($result->reviewer_user_id)->toBe($appealReviewer->id)
        ->and($action->refresh()->reversed_at)->not->toBeNull()
        ->and(ForumModerationAppeal::query()->count())->toBe(1);

    expect(fn () => $review->handle(
        $appealReviewer,
        $result,
        'upheld',
        'A completed appeal cannot receive a second independent decision.',
    ))->toThrow(ValidationException::class);
});

test('report submission is rate limited per reporter', function () {
    $submit = app(SubmitForumReport::class);

    foreach (range(1, 10) as $index) {
        $submit->handle(
            $this->authenticatedUser,
            ForumTopic::factory()->create(),
            'spam',
            "Spam report {$index} with distinct context.",
            true,
        );
    }

    expect(fn () => $submit->handle(
        $this->authenticatedUser,
        ForumTopic::factory()->create(),
        'spam',
        'One report beyond the hourly limit.',
        true,
    ))->toThrow(ValidationException::class);
});
