<?php

declare(strict_types=1);

use App\Actions\AppealForumReviewPanel;
use App\Actions\CreateForumReviewPanel;
use App\Actions\ModerateCommunityNote;
use App\Actions\ProposeCommunityNote;
use App\Actions\RespondToCommunityNote;
use App\Actions\ReviseCommunityNote;
use App\Actions\StartCommunityNoteReview;
use App\Actions\SubmitForumPanelReview;
use App\Data\CommunityNoteData;
use App\Enums\ForumCommunityNoteStatus;
use App\Enums\ForumCommunityNoteType;
use App\Enums\ForumReviewAssignmentState;
use App\Enums\ForumReviewDecision;
use App\Enums\ForumReviewPanelState;
use App\Enums\ForumReviewPanelType;
use App\Livewire\Forum\CommunityNotesPanel;
use App\Models\ForumAnswer;
use App\Models\ForumCommunityNote;
use App\Models\ForumCommunityNoteVersion;
use App\Models\ForumReviewAssignment;
use App\Models\ForumReviewPanel;
use App\Models\ForumReviewPanelEvent;
use App\Models\ForumTopic;
use App\Models\ForumTrustLevel;
use App\Models\ForumUserTrustLevel;
use App\Models\User;
use Database\Seeders\ForumSystemSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(ForumSystemSeeder::class);
});

function grantCommunityReviewLevel(
    User $user,
    string $levelKey = 'community-reviewer',
): ForumUserTrustLevel {
    $level = ForumTrustLevel::query()
        ->where('stable_key', $levelKey)
        ->firstOrFail();

    return ForumUserTrustLevel::factory()->create([
        'user_id' => $user->id,
        'forum_trust_level_id' => $level->id,
        'scope_type' => 'global',
        'scope_key' => 'global',
        'expires_at' => null,
    ]);
}

/** @return list<User> */
function createCommunityReviewers(int $count): array
{
    return User::factory()
        ->count($count)
        ->create([
            'created_at' => now()->subMonths(6),
        ])
        ->each(fn (User $user) => grantCommunityReviewLevel($user))
        ->all();
}

function communityNoteData(
    ForumTopic|ForumAnswer $subject,
    ForumCommunityNoteType $type = ForumCommunityNoteType::MissingContext,
    int $expectedLockVersion = 0,
): CommunityNoteData {
    return new CommunityNoteData(
        subjectType: $subject instanceof ForumTopic ? 'forum-topic' : 'forum-answer',
        subjectId: $subject->id,
        type: $type,
        body: 'This contextual note explains a verifiable limitation without targeting the content author.',
        evidence: [[
            'url' => 'https://example.test/community-review-source',
            'label' => 'Community review source',
        ]],
        jurisdiction: 'LT',
        speciesContext: 'Canis lupus familiaris',
        expectedLockVersion: $expectedLockVersion,
    );
}

test('community review schema has append-only history and leading indexes', function () {
    expect(Schema::hasColumns('forum_review_panels', [
        'subject_type',
        'subject_id',
        'panel_type',
        'risk_class',
        'requested_by_user_id',
        'state',
        'required_reviewers',
        'decision',
        'moderator_override_by_user_id',
        'appealed_by_user_id',
        'appeal_reason',
        'review_deadline_at',
        'active_key',
    ]))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_review_assignments',
            'forum_review_assignments_panel_reviewer_unique',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_review_panel_events',
            'forum_review_panel_events_panel_created_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_community_notes',
            'forum_community_notes_subject_status_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_community_note_versions',
            'forum_community_note_versions_note_version_unique',
        ))->toBeTrue();
});

test('every supported low risk panel type creates an audited panel', function (
    ForumReviewPanelType $panelType,
) {
    $requester = User::factory()->create();
    grantCommunityReviewLevel($requester, 'trusted-contributor');
    createCommunityReviewers(3);
    $topic = ForumTopic::factory()->create();

    $panel = app(CreateForumReviewPanel::class)->handle(
        $requester,
        'forum-topic',
        $topic->id,
        $panelType,
    );

    expect($panel->panel_type)->toBe($panelType)
        ->and($panel->risk_class)->toBe('low')
        ->and($panel->assignments)->toHaveCount(3)
        ->and($panel->events)->toHaveCount(1)
        ->and($panel->public_context)->not->toHaveKey('private_evidence');
})->with(ForumReviewPanelType::cases());

test('high risk decisions cannot be represented as community panel types', function (
    string $prohibitedType,
) {
    expect(ForumReviewPanelType::tryFrom($prohibitedType))->toBeNull();
})->with([
    'threats',
    'child-safety',
    'private-personal-data',
    'serious-harassment',
    'animal-cruelty-evidence',
    'illegal-trade',
    'professional-credential-fraud',
    'severe-medical-misinformation',
    'legal-demands',
    'private-payment-dispute',
    'permanent-account-ban',
]);

test('reviewer assignment is balanced and excludes requester and content author', function () {
    $author = User::factory()->create();
    $requester = User::factory()->create();
    grantCommunityReviewLevel($requester, 'trusted-contributor');
    $reviewers = createCommunityReviewers(4);
    $existingPanel = ForumReviewPanel::factory()->create();
    ForumReviewAssignment::factory()->create([
        'forum_review_panel_id' => $existingPanel->id,
        'reviewer_user_id' => $reviewers[0]->id,
        'state' => ForumReviewAssignmentState::Assigned,
    ]);
    $topic = ForumTopic::factory()->create([
        'author_id' => $author->id,
        'author_key' => $author->actor_key,
    ]);

    $panel = app(CreateForumReviewPanel::class)->handle(
        $requester,
        'forum-topic',
        $topic->id,
        ForumReviewPanelType::WrongCategory,
    );
    $assignedIds = $panel->assignments()->pluck('reviewer_user_id')->all();

    expect($assignedIds)->not->toContain($author->id)
        ->not->toContain($requester->id)
        ->not->toContain($reviewers[0]->id)
        ->toHaveCount(3);
});

test('untrusted users cannot propose notes or panels', function () {
    $user = User::factory()->create();
    $topic = ForumTopic::factory()->create();

    expect(fn () => app(ProposeCommunityNote::class)->handle(
        $user,
        communityNoteData($topic),
    ))->toThrow(AuthorizationException::class)
        ->and(fn () => app(CreateForumReviewPanel::class)->handle(
            $user,
            'forum-topic',
            $topic->id,
            ForumReviewPanelType::Tag,
        ))->toThrow(AuthorizationException::class);
});

test('topic and answer notes preserve evidence and immutable initial versions', function (
    string $subjectType,
) {
    $proposer = User::factory()->create();
    grantCommunityReviewLevel($proposer, 'trusted-contributor');
    $topic = ForumTopic::factory()->create();
    $subject = $subjectType === 'forum-answer'
        ? ForumAnswer::factory()->create(['topic_id' => $topic->id])
        : $topic;

    $note = app(ProposeCommunityNote::class)->handle(
        $proposer,
        communityNoteData($subject),
    );

    expect($note->subject_type)->toBe($subjectType)
        ->and($note->subject_id)->toBe($subject->id)
        ->and($note->status)->toBe(ForumCommunityNoteStatus::Proposed)
        ->and($note->versions)->toHaveCount(1)
        ->and($note->evidence[0]['url'])->toBe('https://example.test/community-review-source');
})->with(['forum-topic', 'forum-answer']);

test('every contextual note purpose is represented by a validated note type', function (
    ForumCommunityNoteType $noteType,
) {
    $proposer = User::factory()->create();
    grantCommunityReviewLevel($proposer, 'trusted-contributor');
    $topic = ForumTopic::factory()->create();

    $note = app(ProposeCommunityNote::class)->handle(
        $proposer,
        communityNoteData($topic, $noteType),
    );

    expect($note->note_type)->toBe($noteType)
        ->and($note->is_safety_notice)->toBe($noteType->isSafetySensitive());
})->with(ForumCommunityNoteType::cases());

test('community note revisions enforce evidence and optimistic locking', function () {
    $proposer = User::factory()->create();
    grantCommunityReviewLevel($proposer, 'trusted-contributor');
    $topic = ForumTopic::factory()->create();
    $note = app(ProposeCommunityNote::class)->handle(
        $proposer,
        communityNoteData($topic),
    );
    $invalidEvidence = new CommunityNoteData(
        subjectType: 'forum-topic',
        subjectId: $topic->id,
        type: ForumCommunityNoteType::SourceCorrection,
        body: 'This revised note has sufficient prose but intentionally lacks required evidence.',
        evidence: [],
        jurisdiction: null,
        speciesContext: null,
        expectedLockVersion: 0,
    );

    expect(fn () => app(ReviseCommunityNote::class)->handle(
        $proposer,
        $note,
        $invalidEvidence,
        'Source correction',
    ))->toThrow(ValidationException::class);

    $staleRevision = communityNoteData(
        $topic,
        ForumCommunityNoteType::SourceCorrection,
        expectedLockVersion: 99,
    );

    expect(fn () => app(ReviseCommunityNote::class)->handle(
        $proposer,
        $note,
        $staleRevision,
        'Source correction',
    ))->toThrow(ValidationException::class)
        ->and($note->refresh()->current_version)->toBe(1);
});

test('revision cancels stale assignments and permits a fresh independent review', function () {
    $proposer = User::factory()->create();
    grantCommunityReviewLevel($proposer, 'trusted-contributor');
    createCommunityReviewers(3);
    $topic = ForumTopic::factory()->create();
    $note = app(ProposeCommunityNote::class)->handle(
        $proposer,
        communityNoteData($topic),
    );
    $note = app(StartCommunityNoteReview::class)->handle($proposer, $note);
    $oldPanel = $note->reviewPanel;
    $revision = communityNoteData(
        $topic,
        ForumCommunityNoteType::SourceCorrection,
        expectedLockVersion: $note->lock_version,
    );

    $note = app(ReviseCommunityNote::class)->handle(
        $proposer,
        $note,
        $revision,
        'Evidence changed',
    );

    expect($note->status)->toBe(ForumCommunityNoteStatus::GatheringEvidence)
        ->and($note->forum_review_panel_id)->toBeNull()
        ->and($oldPanel->refresh()->state)->toBe(ForumReviewPanelState::Cancelled)
        ->and($oldPanel->assignments()
            ->where('state', ForumReviewAssignmentState::Cancelled->value)
            ->count())->toBe(3)
        ->and($oldPanel->events()->where('event_type', 'cancelled')->count())->toBe(1);

    $note = app(StartCommunityNoteReview::class)->handle($proposer, $note);

    expect($note->forum_review_panel_id)->not->toBe($oldPanel->id)
        ->and($note->status)->toBe(ForumCommunityNoteStatus::InReview);
});

test('moderator workflow records every review and terminal note outcome', function (
    ForumCommunityNoteStatus $targetStatus,
) {
    $administrator = User::factory()->administrator()->create();
    $note = ForumCommunityNote::factory()->create();

    $moderated = app(ModerateCommunityNote::class)->handle(
        $administrator,
        $note,
        $targetStatus,
        'The moderator reviewed the available evidence and recorded this explicit workflow outcome.',
    );

    expect($moderated->status)->toBe($targetStatus)
        ->and($moderated->current_version)->toBe(2)
        ->and($moderated->versions)->toHaveCount(1);

    if ($targetStatus->isPublic()) {
        expect($moderated->published_at)->not->toBeNull();
    }

    if ($targetStatus === ForumCommunityNoteStatus::RevalidationDue) {
        expect($moderated->revalidation_due_at)->not->toBeNull()
            ->and($moderated->revalidation_due_at->isPast())->toBeTrue();
    }
})->with([
    ForumCommunityNoteStatus::ModeratorReview,
    ForumCommunityNoteStatus::Published,
    ForumCommunityNoteStatus::Revised,
    ForumCommunityNoteStatus::Rejected,
    ForumCommunityNoteStatus::Archived,
    ForumCommunityNoteStatus::RevalidationDue,
]);

test('administrator revision revalidates a published note without rewriting history', function () {
    $administrator = User::factory()->administrator()->create();
    $topic = ForumTopic::factory()->create();
    $note = ForumCommunityNote::factory()->published()->create([
        'subject_type' => 'forum-topic',
        'subject_id' => $topic->id,
        'current_version' => 1,
        'lock_version' => 0,
        'revalidation_due_at' => now()->subDay(),
    ]);

    $revalidated = app(ReviseCommunityNote::class)->handle(
        $administrator,
        $note,
        communityNoteData(
            $topic,
            ForumCommunityNoteType::SourceCorrection,
            expectedLockVersion: 0,
        ),
        'Scheduled source revalidation',
    );

    expect($revalidated->status)->toBe(ForumCommunityNoteStatus::Revised)
        ->and($revalidated->revalidation_due_at->isFuture())->toBeTrue()
        ->and($revalidated->current_version)->toBe(2)
        ->and($revalidated->versions)->toHaveCount(1);
});

test('note review reaches quorum once and records an immutable assessment', function () {
    $proposer = User::factory()->create();
    grantCommunityReviewLevel($proposer, 'trusted-contributor');
    createCommunityReviewers(3);
    $note = app(ProposeCommunityNote::class)->handle(
        $proposer,
        communityNoteData(ForumTopic::factory()->create()),
    );
    $note = app(StartCommunityNoteReview::class)->handle($proposer, $note);
    $assignments = $note->reviewPanel->assignments()->get();
    $review = app(SubmitForumPanelReview::class);

    foreach ($assignments as $assignment) {
        $reviewer = User::query()->findOrFail($assignment->reviewer_user_id);
        $review->handle(
            $reviewer,
            $assignment,
            ForumReviewDecision::Support,
            'The source and wording support this limited contextual note.',
        );
    }

    expect($note->reviewPanel->refresh()->state)->toBe(ForumReviewPanelState::Decided)
        ->and($note->reviewPanel->decision)->toBe(ForumReviewDecision::Support)
        ->and($note->refresh()->status)->toBe(ForumCommunityNoteStatus::CommunityAssessed)
        ->and($note->versions)->toHaveCount(3)
        ->and(ForumReviewPanelEvent::query()
            ->where('forum_review_panel_id', $note->forum_review_panel_id)
            ->where('event_type', 'decision-reached')
            ->count())->toBe(1);

    expect(fn () => $review->handle(
        User::query()->findOrFail($assignments[0]->reviewer_user_id),
        $assignments[0],
        ForumReviewDecision::Support,
        'A duplicate decision must fail even when the reasoning is valid.',
    ))->toThrow(ValidationException::class);
});

test('conflicted reviewer is recused and replaced without contributing a vote', function () {
    $proposer = User::factory()->create();
    grantCommunityReviewLevel($proposer, 'trusted-contributor');
    createCommunityReviewers(4);
    $note = app(ProposeCommunityNote::class)->handle(
        $proposer,
        communityNoteData(ForumTopic::factory()->create()),
    );
    $note = app(StartCommunityNoteReview::class)->handle($proposer, $note);
    $assignment = $note->reviewPanel->assignments()->firstOrFail();
    $reviewer = User::query()->findOrFail($assignment->reviewer_user_id);

    app(SubmitForumPanelReview::class)->handle(
        $reviewer,
        $assignment,
        ForumReviewDecision::Abstain,
        'I work for the organization discussed and cannot review independently.',
        hasConflict: true,
        conflictType: 'same-organization',
    );

    expect($assignment->refresh()->state)->toBe(ForumReviewAssignmentState::Recused)
        ->and($note->reviewPanel->assignments()->count())->toBe(4)
        ->and($note->reviewPanel->assignments()
            ->whereNotNull('replacement_for_assignment_id')
            ->count())->toBe(1)
        ->and($note->reviewPanel->decision)->toBeNull();
});

test('expired assignments fail closed without a scheduler', function () {
    $reviewer = User::factory()->create();
    grantCommunityReviewLevel($reviewer);
    $panel = ForumReviewPanel::factory()->create([
        'state' => ForumReviewPanelState::InReview,
        'review_deadline_at' => now()->subMinute(),
    ]);
    $assignment = ForumReviewAssignment::factory()->create([
        'forum_review_panel_id' => $panel->id,
        'reviewer_user_id' => $reviewer->id,
        'review_deadline_at' => now()->subMinute(),
    ]);

    expect(fn () => app(SubmitForumPanelReview::class)->handle(
        $reviewer,
        $assignment,
        ForumReviewDecision::Support,
        'This review arrived after the configured panel deadline.',
    ))->toThrow(ValidationException::class)
        ->and($panel->refresh()->state)->toBe(ForumReviewPanelState::Expired)
        ->and($panel->active_key)->toBeNull();
});

test('content author can respond but cannot remove an approved safety note', function () {
    $author = User::factory()->create();
    $proposer = User::factory()->create();
    $administrator = User::factory()->administrator()->create();
    grantCommunityReviewLevel($proposer, 'trusted-contributor');
    $topic = ForumTopic::factory()->create([
        'author_id' => $author->id,
        'author_key' => $author->actor_key,
    ]);
    $note = app(ProposeCommunityNote::class)->handle(
        $proposer,
        communityNoteData($topic, ForumCommunityNoteType::SafetyWarning),
    );
    $note->forceFill(['status' => ForumCommunityNoteStatus::InReview])->save();

    $responded = app(RespondToCommunityNote::class)->handle(
        $author,
        $note,
        'Thank you. The original post has been clarified while preserving this review history.',
    );
    $published = app(ModerateCommunityNote::class)->handle(
        $administrator,
        $responded,
        ForumCommunityNoteStatus::Published,
        'The safety context is sourced, proportionate, and should remain visible.',
    );

    expect($published->author_response)->toContain('original post')
        ->and($published->is_safety_notice)->toBeTrue()
        ->and($published->versions)->toHaveCount(3)
        ->and(Gate::forUser($author)->allows('update', $published))->toBeFalse()
        ->and(Gate::forUser($author)->allows('delete', $published))->toBeFalse();
});

test('moderator publication and appeal retain complete panel history', function () {
    $proposer = User::factory()->create();
    $administrator = User::factory()->administrator()->create();
    grantCommunityReviewLevel($proposer, 'trusted-contributor');
    createCommunityReviewers(3);
    $note = app(ProposeCommunityNote::class)->handle(
        $proposer,
        communityNoteData(ForumTopic::factory()->create()),
    );
    $note = app(StartCommunityNoteReview::class)->handle($proposer, $note);

    app(ModerateCommunityNote::class)->handle(
        $administrator,
        $note,
        ForumCommunityNoteStatus::Published,
        'The independent evidence is sufficient for a public contextual note.',
    );
    $panel = $note->reviewPanel->refresh();
    app(AppealForumReviewPanel::class)->handle(
        $proposer,
        $panel,
        'The moderator outcome should be reviewed against additional source context.',
    );

    expect($note->refresh()->status)->toBe(ForumCommunityNoteStatus::Published)
        ->and($panel->refresh()->state)->toBe(ForumReviewPanelState::Appealed)
        ->and($panel->appealed_by_user_id)->toBe($proposer->id)
        ->and($panel->events()->pluck('event_type')->all())
        ->toContain('created', 'moderator-decision', 'appealed');
});

test('append-only panel events and note versions cannot be rewritten', function () {
    $event = ForumReviewPanelEvent::factory()->create();
    $version = ForumCommunityNoteVersion::factory()->create();

    expect(fn () => $event->update(['reason_code' => 'rewritten']))
        ->toThrow(LogicException::class)
        ->and(fn () => $event->delete())
        ->toThrow(LogicException::class)
        ->and(fn () => $version->update(['change_reason' => 'rewritten']))
        ->toThrow(LogicException::class)
        ->and(fn () => $version->delete())
        ->toThrow(LogicException::class);
});

test('database uniqueness rejects duplicate reviewers and duplicate versions', function () {
    $assignment = ForumReviewAssignment::factory()->create();
    $version = ForumCommunityNoteVersion::factory()->create();

    expect(fn () => ForumReviewAssignment::factory()->create([
        'forum_review_panel_id' => $assignment->forum_review_panel_id,
        'reviewer_user_id' => $assignment->reviewer_user_id,
    ]))->toThrow(QueryException::class)
        ->and(fn () => ForumCommunityNoteVersion::factory()->create([
            'forum_community_note_id' => $version->forum_community_note_id,
            'version_number' => $version->version_number,
        ]))->toThrow(QueryException::class);
});

test('public forum exposes only published notes while trusted livewire actions authorize directly', function () {
    $topic = ForumTopic::factory()->create();
    $trusted = User::factory()->create();
    grantCommunityReviewLevel($trusted, 'trusted-contributor');
    createCommunityReviewers(3);
    $draft = ForumCommunityNote::factory()->create([
        'subject_type' => 'forum-topic',
        'subject_id' => $topic->id,
        'body' => 'Private proposed note should not be shown to a guest visitor.',
    ]);
    $published = ForumCommunityNote::factory()->published()->create([
        'subject_type' => 'forum-topic',
        'subject_id' => $topic->id,
        'body' => 'Published reviewed context is visible to every authorized topic reader.',
    ]);

    Livewire::test(CommunityNotesPanel::class, ['topicId' => $topic->id])
        ->assertSee($published->body)
        ->assertDontSee($draft->body)
        ->assertDontSee(__('forum_review.notes.propose_heading'));

    $component = Livewire::actingAs($trusted)
        ->test(CommunityNotesPanel::class, ['topicId' => $topic->id])
        ->assertSee(__('forum_review.notes.propose_heading'))
        ->set('form.noteType', ForumCommunityNoteType::SourceCorrection->value)
        ->set('form.body', 'The current source is outdated and this replacement clarifies the limited factual claim.')
        ->set('form.evidenceUrl', 'https://example.test/replacement-source')
        ->set('form.evidenceLabel', 'Replacement source')
        ->call('propose')
        ->assertHasNoErrors()
        ->assertSee(__('forum_review.feedback.proposed'));

    expect(ForumCommunityNote::query()
        ->where('proposer_user_id', $trusted->id)
        ->where('note_type', ForumCommunityNoteType::SourceCorrection->value)
        ->exists())->toBeTrue();

    expect(fn () => $component->set('topicId', ForumTopic::factory()->create()->id))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

test('an outsider cannot submit another reviewers livewire assignment', function () {
    $proposer = User::factory()->create();
    grantCommunityReviewLevel($proposer, 'trusted-contributor');
    createCommunityReviewers(3);
    $note = app(ProposeCommunityNote::class)->handle(
        $proposer,
        communityNoteData(ForumTopic::factory()->create()),
    );
    $note = app(StartCommunityNoteReview::class)->handle($proposer, $note);
    $assignment = $note->reviewPanel->assignments()->firstOrFail();
    $outsider = User::factory()->create();
    grantCommunityReviewLevel($outsider);

    Livewire::actingAs($outsider)
        ->test(CommunityNotesPanel::class, ['topicId' => $note->subject_id])
        ->set('reviewDecision', ForumReviewDecision::Support->value)
        ->set('reviewReasoning', 'This is long enough but belongs to a different reviewer.')
        ->call('submitReview', $assignment->id)
        ->assertForbidden();
});
