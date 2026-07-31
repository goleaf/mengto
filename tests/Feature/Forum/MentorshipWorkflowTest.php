<?php

declare(strict_types=1);

use App\Actions\EndMentorship;
use App\Actions\RequestMentorship;
use App\Actions\RespondToMentorship;
use App\Actions\SaveMentorScope;
use App\Actions\SendMentorshipMessage;
use App\Actions\SubmitForumReport;
use App\Actions\SubmitMentorshipFeedback;
use App\Actions\UpdateMentorProfile;
use App\Actions\ValidateMentorshipCompletion;
use App\Data\MentorProfileData;
use App\Data\MentorScopeData;
use App\Data\MentorshipRequestData;
use App\Enums\ForumMentorProfileState;
use App\Enums\ForumMentorshipState;
use App\Enums\ForumMentorshipType;
use App\Livewire\Forum\MentorDiscovery;
use App\Livewire\Forum\MentorProfileManager;
use App\Livewire\Forum\MentorshipInbox;
use App\Models\Credential;
use App\Models\ExpertProfile;
use App\Models\ForumBadge;
use App\Models\ForumBlock;
use App\Models\ForumMentorProfile;
use App\Models\ForumMentorScope;
use App\Models\ForumMentorship;
use App\Models\ForumMentorshipEvent;
use App\Models\ForumMentorshipFeedback;
use App\Models\ForumMentorshipMessage;
use App\Models\ForumReport;
use App\Models\ForumReputationEvent;
use App\Models\ForumTrustLevel;
use App\Models\ForumUserBadge;
use App\Models\ForumUserTrustLevel;
use App\Models\User;
use App\Services\MentorMatcher;
use App\Services\MentorshipEligibility;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ForumSystemSeeder;
use Database\Seeders\MentorshipDemoSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(ForumSystemSeeder::class);
});

function grantMentorTrust(User $user, string $key = 'mentor'): ForumUserTrustLevel
{
    $level = ForumTrustLevel::query()->where('stable_key', $key)->firstOrFail();

    return ForumUserTrustLevel::factory()->create([
        'user_id' => $user->id,
        'forum_trust_level_id' => $level->id,
        'scope_type' => 'global',
        'scope_key' => 'global',
        'expires_at' => null,
    ]);
}

function mentorProfileData(
    int $lockVersion = 0,
    ForumMentorProfileState $state = ForumMentorProfileState::Active,
): MentorProfileData {
    return new MentorProfileData(
        state: $state,
        headline: 'Practical peer support for responsible animal care',
        summary: 'I share peer experience, respect professional boundaries, and keep all communication on the platform.',
        languages: ['en', 'lt'],
        locationScope: 'lt-vilnius',
        timezone: 'Europe/Vilnius',
        communicationPreferences: ['platform'],
        availability: ['weekdays' => ['evening']],
        capacity: 2,
        isPublic: true,
        safetyAcknowledged: true,
        expectedLockVersion: $lockVersion,
    );
}

/**
 * @return array{0: User, 1: ForumMentorProfile, 2: ForumMentorScope}
 */
function createMentorAndScope(
    ForumMentorshipType $type = ForumMentorshipType::FirstTimeOwner,
    bool $requiresVerifiedExpertise = false,
): array {
    $mentor = User::factory()->create();
    grantMentorTrust($mentor);
    $profile = app(UpdateMentorProfile::class)->handle($mentor, mentorProfileData());
    $scope = app(SaveMentorScope::class)->handle(
        $mentor,
        $profile,
        new MentorScopeData(
            type: $type,
            experienceSummary: 'Several years of practical peer support with clear referral boundaries.',
            forumCategoryId: null,
            taxonId: null,
            requiresVerifiedExpertise: $requiresVerifiedExpertise,
        ),
    );

    return [$mentor, $profile, $scope];
}

function mentorshipRequestData(string $key): MentorshipRequestData
{
    return new MentorshipRequestData(
        message: 'I would like structured peer support while preparing a safe routine for my animal.',
        language: 'en',
        locationScope: 'lt-vilnius',
        communicationPreference: 'platform',
        safetyAcknowledged: true,
        idempotencyKey: $key,
    );
}

function activeMentorship(
    string $key = 'mentorship-request-idempotency-key',
): ForumMentorship {
    [$mentor, , $scope] = createMentorAndScope();
    $mentee = User::factory()->create();
    $mentorship = app(RequestMentorship::class)->handle(
        $mentee,
        $scope,
        mentorshipRequestData($key),
    );

    return app(RespondToMentorship::class)->handle(
        $mentor,
        $mentorship,
        accept: true,
        response: 'I can help within the peer-support boundaries.',
        safetyAcknowledged: true,
        expectedLockVersion: 0,
    );
}

test('mentorship schema provides lifecycle integrity and leading indexes', function () {
    expect(Schema::hasColumns('forum_mentor_profiles', [
        'user_id',
        'state',
        'languages',
        'communication_preferences',
        'capacity',
        'safety_acknowledged_at',
        'lock_version',
    ]))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_mentor_scopes',
            'forum_mentor_scopes_profile_active_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_mentorships',
            'forum_mentorships_mentor_state_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_mentorship_messages',
            'forum_mentorship_messages_thread_idx',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_mentorship_feedback',
            'forum_mentorship_feedback_author_unique',
        ))->toBeTrue()
        ->and(Schema::hasIndex(
            'forum_mentorship_events',
            'forum_mentorship_events_history_idx',
        ))->toBeTrue();
});

test('all required mentorship types are represented', function () {
    expect(ForumMentorshipType::cases())->toHaveCount(13)
        ->and(array_column(ForumMentorshipType::cases(), 'value'))->toBe([
            'first-time-owner',
            'new-species-owner',
            'adoption-adaptation',
            'foster-support',
            'training-support',
            'senior-animal-care',
            'special-needs-care',
            'aquarium-setup',
            'terrarium-setup',
            'horse-ownership',
            'farm-animal-care',
            'lost-animal-search',
            'volunteer-onboarding',
        ]);
});

test('only verified trusted users can activate mentor profiles', function () {
    $ordinary = User::factory()->create();
    $unverified = User::factory()->unverified()->create();
    grantMentorTrust($unverified);

    expect(fn () => app(UpdateMentorProfile::class)->handle(
        $ordinary,
        mentorProfileData(),
    ))->toThrow(AuthorizationException::class)
        ->and(fn () => app(UpdateMentorProfile::class)->handle(
            $unverified,
            mentorProfileData(),
        ))->toThrow(AuthorizationException::class);

    grantMentorTrust($ordinary);
    $profile = app(UpdateMentorProfile::class)->handle($ordinary, mentorProfileData());

    expect($profile->state)->toBe(ForumMentorProfileState::Active)
        ->and($profile->safety_acknowledged_at)->not->toBeNull();
});

test('mentor profile updates enforce optimistic locking and withdrawal pauses scopes', function () {
    [$mentor, $profile, $scope] = createMentorAndScope();

    expect(fn () => app(UpdateMentorProfile::class)->handle(
        $mentor,
        mentorProfileData(0),
    ))->toThrow(ValidationException::class);

    app(UpdateMentorProfile::class)->handle(
        $mentor,
        mentorProfileData(
            $profile->lock_version,
            ForumMentorProfileState::Withdrawn,
        ),
    );

    expect($scope->refresh()->is_active)->toBeFalse();
});

test('mentor scopes are stable idempotent and owned by the profile user', function () {
    [$mentor, $profile, $scope] = createMentorAndScope();
    $data = new MentorScopeData(
        type: ForumMentorshipType::FirstTimeOwner,
        experienceSummary: 'Updated practical experience with the same stable peer-support scope.',
        forumCategoryId: null,
        taxonId: null,
        requiresVerifiedExpertise: false,
    );
    $updated = app(SaveMentorScope::class)->handle($mentor, $profile, $data);

    expect($updated->id)->toBe($scope->id)
        ->and(ForumMentorScope::query()->count())->toBe(1)
        ->and(fn () => app(SaveMentorScope::class)->handle(
            User::factory()->create(),
            $profile,
            $data,
        ))->toThrow(AuthorizationException::class);
});

test('professional scopes require independent current credentials', function () {
    $mentor = User::factory()->create();
    grantMentorTrust($mentor);
    $profile = app(UpdateMentorProfile::class)->handle($mentor, mentorProfileData());
    $data = new MentorScopeData(
        type: ForumMentorshipType::SpecialNeedsCare,
        experienceSummary: 'Professional-scope support backed by a separately reviewed credential.',
        forumCategoryId: null,
        taxonId: null,
        requiresVerifiedExpertise: true,
    );

    expect(fn () => app(SaveMentorScope::class)->handle(
        $mentor,
        $profile,
        $data,
    ))->toThrow(ValidationException::class);

    $expert = ExpertProfile::factory()->create([
        'owner_id' => $mentor->id,
        'owner_key' => $mentor->actor_key,
    ]);
    Credential::factory()->create(['expert_profile_id' => $expert->id]);
    $professionalScope = app(SaveMentorScope::class)->handle(
        $mentor,
        $profile,
        $data,
    );

    expect($professionalScope->requires_verified_expertise)->toBeTrue();
});

test('matcher is bounded transparent and does not infer professional status from trust', function () {
    [$mentor, , $scope] = createMentorAndScope();
    $requester = User::factory()->create();

    $matches = app(MentorMatcher::class)->find(
        requester: $requester,
        type: ForumMentorshipType::FirstTimeOwner,
        language: 'en',
        communicationPreference: 'platform',
        locationScope: 'lt-vilnius',
    );

    expect($matches)->toHaveCount(1)
        ->and($matches->first()->scopeId)->toBe($scope->id)
        ->and($matches->first()->mentorUserId)->toBe($mentor->id)
        ->and($matches->first()->professionallyVerified)->toBeFalse()
        ->and($matches->first()->reasonTranslationKeys)->toContain(
            'forum_mentorship.match_reasons.type',
            'forum_mentorship.match_reasons.location',
        );
});

test('matcher excludes blocked mentors and mentors at capacity', function () {
    [$mentor, $profile] = createMentorAndScope();
    $requester = User::factory()->create();
    ForumBlock::factory()->create([
        'user_key' => $requester->actor_key,
        'blocked_author_key' => $mentor->actor_key,
    ]);

    expect(app(MentorMatcher::class)->find(
        $requester,
        ForumMentorshipType::FirstTimeOwner,
        'en',
        'platform',
    ))->toBeEmpty();

    ForumBlock::query()->delete();
    $profile->update(['capacity' => 1]);
    ForumMentorship::factory()->active()->create([
        'forum_mentor_scope_id' => $profile->scopes()->firstOrFail()->id,
    ]);

    expect(app(MentorMatcher::class)->find(
        $requester,
        ForumMentorshipType::FirstTimeOwner,
        'en',
        'platform',
    ))->toBeEmpty();
});

test('mentorship requests are idempotent and prevent self and duplicate open requests', function () {
    [$mentor, , $scope] = createMentorAndScope();
    $mentee = User::factory()->create();
    $data = mentorshipRequestData('request-idempotency-key-0001');
    $request = app(RequestMentorship::class)->handle($mentee, $scope, $data);
    $same = app(RequestMentorship::class)->handle($mentee, $scope, $data);

    expect($same->id)->toBe($request->id)
        ->and($request->events)->toHaveCount(1)
        ->and(fn () => app(RequestMentorship::class)->handle(
            $mentee,
            $scope,
            mentorshipRequestData('request-idempotency-key-0002'),
        ))->toThrow(ValidationException::class)
        ->and(fn () => app(RequestMentorship::class)->handle(
            $mentor,
            $scope,
            mentorshipRequestData('request-idempotency-key-0003'),
        ))->toThrow(ValidationException::class);
});

test('mentor acceptance and decline are authorized audited and lock protected', function () {
    [$mentor, , $scope] = createMentorAndScope();
    $mentee = User::factory()->create();
    $request = app(RequestMentorship::class)->handle(
        $mentee,
        $scope,
        mentorshipRequestData('response-request-key-0001'),
    );

    expect(fn () => app(RespondToMentorship::class)->handle(
        User::factory()->create(),
        $request,
        true,
        'Unauthorized response.',
        true,
        0,
    ))->toThrow(AuthorizationException::class);

    $accepted = app(RespondToMentorship::class)->handle(
        $mentor,
        $request,
        true,
        'Accepted with peer-support boundaries.',
        true,
        0,
    );

    expect($accepted->state)->toBe(ForumMentorshipState::Active)
        ->and($accepted->events()->count())->toBe(2)
        ->and(fn () => app(RespondToMentorship::class)->handle(
            $mentor,
            $accepted,
            false,
            'Stale response.',
            false,
            0,
        ))->toThrow(AuthorizationException::class);
});

test('mentor can decline and mentee can cancel a pending request', function () {
    [$mentor, , $scope] = createMentorAndScope();
    $firstMentee = User::factory()->create();
    $declined = app(RequestMentorship::class)->handle(
        $firstMentee,
        $scope,
        mentorshipRequestData('decline-request-idempotency-key'),
    );
    $declined = app(RespondToMentorship::class)->handle(
        $mentor,
        $declined,
        false,
        'I cannot offer this peer support safely at present.',
        false,
        0,
    );

    expect($declined->state)->toBe(ForumMentorshipState::Declined)
        ->and($declined->open_key)->toBeNull();

    $secondMentee = User::factory()->create();
    $cancelled = app(RequestMentorship::class)->handle(
        $secondMentee,
        $scope,
        mentorshipRequestData('cancel-request-idempotency-key'),
    );
    $cancelled = app(EndMentorship::class)->handle(
        $secondMentee,
        $cancelled,
        false,
        'I no longer need this request.',
        false,
        0,
    );

    expect($cancelled->state)->toBe(ForumMentorshipState::Cancelled)
        ->and($cancelled->open_key)->toBeNull();
});

test('only active participants can use the private idempotent thread', function () {
    $mentorship = activeMentorship('private-thread-request-key');
    $mentor = $mentorship->mentor;
    $mentee = $mentorship->mentee;
    $key = 'private-message-idempotency-key';
    $message = app(SendMentorshipMessage::class)->handle(
        $mentee,
        $mentorship,
        '<script>alert(1)</script> Please explain the first safe step.',
        $key,
    );
    $same = app(SendMentorshipMessage::class)->handle(
        $mentee,
        $mentorship,
        'A duplicate payload cannot replace the original.',
        $key,
    );

    expect($same->id)->toBe($message->id)
        ->and($same->body)->toContain('<script>')
        ->and(Gate::forUser(User::factory()->create())->allows('view', $mentorship))->toBeFalse()
        ->and(fn () => app(SendMentorshipMessage::class)->handle(
            User::factory()->create(),
            $mentorship,
            'This outsider message must be rejected.',
            'outsider-message-idempotency',
        ))->toThrow(AuthorizationException::class);

    ForumBlock::factory()->create([
        'user_key' => $mentor->actor_key,
        'blocked_author_key' => $mentee->actor_key,
    ]);

    expect(fn () => app(SendMentorshipMessage::class)->handle(
        $mentor,
        $mentorship,
        'Blocked participants cannot continue contact.',
        'blocked-message-idempotency-key',
    ))->toThrow(ValidationException::class);
});

test('participants can end and block without deleting history', function () {
    $mentorship = activeMentorship('end-request-idempotency-key');
    $mentee = $mentorship->mentee;
    $ended = app(EndMentorship::class)->handle(
        $mentee,
        $mentorship,
        completed: false,
        reason: 'I am ending this peer-support relationship.',
        blockCounterpart: true,
        expectedLockVersion: 1,
    );

    expect($ended->state)->toBe(ForumMentorshipState::Ended)
        ->and($ended->open_key)->toBeNull()
        ->and($ended->events()->where('event_type', 'blocked')->exists())->toBeTrue()
        ->and(ForumBlock::query()->where(
            'user_key',
            $mentee->actor_key,
        )->exists())->toBeTrue();
});

test('mentorship reports are private participant-only and can block the counterpart', function () {
    $mentorship = activeMentorship('report-request-idempotency-key');
    $mentee = $mentorship->mentee;
    $outsider = User::factory()->create();

    expect(fn () => app(SubmitForumReport::class)->handle(
        reporter: $outsider,
        subject: $mentorship,
        reasonKey: 'harassment',
        details: 'An outsider must not enumerate a private mentorship.',
        truthfulnessConfirmed: true,
    ))->toThrow(AuthorizationException::class);

    $report = app(SubmitForumReport::class)->handle(
        reporter: $mentee,
        subject: $mentorship,
        reasonKey: 'harassment',
        details: 'Private evidence is available to authorized moderators.',
        truthfulnessConfirmed: true,
        blockAffectedUser: true,
    );

    expect($report->subject_type)->toBe(ForumMentorship::class)
        ->and($report->affected_user_id)->toBe($mentorship->mentor_user_id)
        ->and($report->reporter_id)->toBe($mentee->id)
        ->and($mentorship->events()->where('event_type', 'reported')->exists())->toBeTrue();
});

test('each participant can submit only one immutable optional feedback record', function () {
    $mentorship = activeMentorship('feedback-request-idempotency-key');
    $mentee = $mentorship->mentee;
    $completed = app(EndMentorship::class)->handle(
        $mentee,
        $mentorship,
        completed: true,
        reason: 'The peer-support goal is complete.',
        blockCounterpart: false,
        expectedLockVersion: 1,
    );
    $feedback = app(SubmitMentorshipFeedback::class)->handle(
        $mentee,
        $completed,
        rating: 5,
        summary: 'Clear and respectful peer support.',
        wouldRecommend: true,
        privateNote: 'Private moderation-only context.',
    );

    expect($feedback->recipient_user_id)->toBe($completed->mentor_user_id)
        ->and($feedback->getHidden())->toContain('private_note')
        ->and(fn () => $feedback->update(['rating' => 1]))->toThrow(LogicException::class)
        ->and(fn () => app(SubmitMentorshipFeedback::class)->handle(
            $mentee,
            $completed,
            4,
            'A duplicate feedback record is not allowed.',
            true,
            null,
        ))->toThrow(ValidationException::class);
});

test('completion validation independently grants idempotent reputation and mentor badge', function () {
    $mentorship = activeMentorship('validation-request-idempotency-key');
    $mentor = $mentorship->mentor;
    $mentee = $mentorship->mentee;
    app(SendMentorshipMessage::class)->handle(
        $mentor,
        $mentorship,
        'Here is the first bounded peer-support step.',
        'validation-message-mentor',
    );
    app(SendMentorshipMessage::class)->handle(
        $mentee,
        $mentorship,
        'I completed that step and understand the boundaries.',
        'validation-message-mentee',
    );
    $completed = app(EndMentorship::class)->handle(
        $mentee,
        $mentorship,
        completed: true,
        reason: 'The agreed peer-support goal is complete.',
        blockCounterpart: false,
        expectedLockVersion: 1,
    );
    $reviewer = User::factory()->administrator()->create();

    expect(ForumReputationEvent::query()->count())->toBe(0)
        ->and(ForumUserBadge::query()->count())->toBe(0)
        ->and(fn () => app(ValidateMentorshipCompletion::class)->handle(
            $mentor,
            $completed,
        ))->toThrow(AuthorizationException::class);

    $validated = app(ValidateMentorshipCompletion::class)->handle($reviewer, $completed);
    $again = app(ValidateMentorshipCompletion::class)->handle($reviewer, $validated);

    expect($again->completion_validated_at)->not->toBeNull()
        ->and($again->validated_by_user_id)->toBe($reviewer->id)
        ->and(ForumReputationEvent::query()->where(
            'idempotency_key',
            "mentorship:{$completed->id}:reputation",
        )->count())->toBe(1)
        ->and(ForumUserBadge::query()
            ->where('user_id', $mentor->id)
            ->whereHas('badge', fn ($query) => $query->where('stable_key', 'mentor'))
            ->count())->toBe(1)
        ->and(app(MentorshipEligibility::class)
            ->hasCurrentProfessionalVerification($mentor))->toBeFalse();
});

test('completion validation rejects insufficient evidence blocks and open reports', function () {
    $mentorship = activeMentorship('insufficient-request-idempotency-key');
    $completed = app(EndMentorship::class)->handle(
        $mentorship->mentee,
        $mentorship,
        true,
        'Completed without enough independent interaction evidence.',
        false,
        1,
    );
    $reviewer = User::factory()->administrator()->create();

    expect(fn () => app(ValidateMentorshipCompletion::class)->handle(
        $reviewer,
        $completed,
    ))->toThrow(ValidationException::class);
});

test('completion validation rejects an open report after sufficient interaction', function () {
    $mentorship = activeMentorship('reported-completion-request-key');
    app(SendMentorshipMessage::class)->handle(
        $mentorship->mentor,
        $mentorship,
        'A mentor message provides interaction evidence.',
        'reported-completion-mentor-message',
    );
    app(SendMentorshipMessage::class)->handle(
        $mentorship->mentee,
        $mentorship,
        'A mentee message provides interaction evidence.',
        'reported-completion-mentee-message',
    );
    $completed = app(EndMentorship::class)->handle(
        $mentorship->mentee,
        $mentorship,
        true,
        'The interaction ended but requires report review.',
        false,
        1,
    );
    app(SubmitForumReport::class)->handle(
        reporter: $completed->mentee,
        subject: $completed,
        reasonKey: 'harassment',
        details: 'This report must be resolved before reputation is granted.',
        truthfulnessConfirmed: true,
    );

    expect(fn () => app(ValidateMentorshipCompletion::class)->handle(
        User::factory()->administrator()->create(),
        $completed,
    ))->toThrow(ValidationException::class);
});

test('message feedback and event histories are append-only', function () {
    $message = ForumMentorshipMessage::factory()->create();
    $feedback = ForumMentorshipFeedback::factory()->create();
    $event = ForumMentorshipEvent::factory()->create();

    expect(fn () => $message->delete())->toThrow(LogicException::class)
        ->and(fn () => $feedback->delete())->toThrow(LogicException::class)
        ->and(fn () => $event->update(['reason_code' => 'rewritten']))
        ->toThrow(LogicException::class);
});

test('database prevents deleting a mentorship with audit history', function () {
    $mentorship = activeMentorship('audit-history-delete-protection-key');
    app(SendMentorshipMessage::class)->handle(
        $mentorship->mentor,
        $mentorship,
        'This message makes the protected audit history explicit.',
        'audit-history-protected-message-key',
    );

    expect(fn () => $mentorship->delete())->toThrow(QueryException::class)
        ->and($mentorship->events()->count())->toBeGreaterThanOrEqual(2)
        ->and($mentorship->messages()->count())->toBe(1);
});

test('database constraints reject duplicate open keys messages feedback and events', function () {
    $mentorship = ForumMentorship::factory()->create();
    $duplicate = $mentorship->replicate();
    $duplicate->idempotency_key = 'different-request-idempotency-key';

    expect(fn () => $duplicate->save())->toThrow(QueryException::class);

    $message = ForumMentorshipMessage::factory()->create([
        'forum_mentorship_id' => $mentorship->id,
        'sender_user_id' => $mentorship->mentor_user_id,
    ]);

    expect(fn () => ForumMentorshipMessage::factory()->create([
        'forum_mentorship_id' => $mentorship->id,
        'sender_user_id' => $mentorship->mentor_user_id,
        'idempotency_key' => $message->idempotency_key,
    ]))->toThrow(QueryException::class);
});

test('all mentorship factories create valid isolated records and enum states', function () {
    $profile = ForumMentorProfile::factory()->create();
    $scope = ForumMentorScope::factory()->create();
    $mentorship = ForumMentorship::factory()->active()->create();
    $message = ForumMentorshipMessage::factory()->create();
    $feedback = ForumMentorshipFeedback::factory()->create();
    $event = ForumMentorshipEvent::factory()->create();

    expect($profile->exists)->toBeTrue()
        ->and($scope->mentorship_type)->toBeInstanceOf(ForumMentorshipType::class)
        ->and($mentorship->state)->toBe(ForumMentorshipState::Active)
        ->and($message->mentorship->state)->toBe(ForumMentorshipState::Active)
        ->and($feedback->recipient_user_id)->not->toBe($feedback->author_user_id)
        ->and($event->exists)->toBeTrue()
        ->and(ForumBadge::query()->where('stable_key', 'mentor')->exists())->toBeTrue()
        ->and(ForumReport::query()->count())->toBe(0);
});

test('allowed-environment mentorship demo seed is idempotent', function () {
    $this->seed(DatabaseSeeder::class);

    $before = [
        'profiles' => ForumMentorProfile::query()->count(),
        'scopes' => ForumMentorScope::query()->count(),
        'mentorships' => ForumMentorship::query()->count(),
        'messages' => ForumMentorshipMessage::query()->count(),
        'events' => ForumMentorshipEvent::query()->count(),
    ];

    $this->seed(MentorshipDemoSeeder::class);

    expect([
        'profiles' => ForumMentorProfile::query()->count(),
        'scopes' => ForumMentorScope::query()->count(),
        'mentorships' => ForumMentorship::query()->count(),
        'messages' => ForumMentorshipMessage::query()->count(),
        'events' => ForumMentorshipEvent::query()->count(),
    ])->toBe($before)
        ->and(ForumMentorship::query()
            ->where('idempotency_key', 'demo-mentorship-first-owner-request-v1')
            ->where('state', ForumMentorshipState::Active->value)
            ->exists())->toBeTrue();
});

test('mentorship page and livewire profile flow are localized and class based', function () {
    $mentor = User::factory()->create();
    grantMentorTrust($mentor);

    $this->actingAs($mentor)
        ->get(route('forum.mentorship.index'))
        ->assertOk()
        ->assertSee(__('forum_mentorship.page.heading'))
        ->assertSee(__('forum_mentorship.safety.boundaries'))
        ->assertDontSee('forum_mentorship.');

    Livewire::actingAs($mentor)
        ->test(MentorProfileManager::class)
        ->set('profileForm.state', ForumMentorProfileState::Active->value)
        ->set('profileForm.headline', 'Accessible peer mentorship profile')
        ->set('profileForm.summary', 'A sufficiently detailed peer support summary with clear safety and professional boundaries.')
        ->set('profileForm.languages', ['en'])
        ->set('profileForm.timezone', 'Europe/Vilnius')
        ->set('profileForm.safetyAcknowledged', true)
        ->call('saveProfile')
        ->assertHasNoErrors()
        ->assertSee(__('forum_mentorship.feedback.profile_saved'));

    expect(ForumMentorProfile::query()->where('user_id', $mentor->id)->exists())
        ->toBeTrue();
});

test('forum navigation exposes mentorship and its first render stays within query budget', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('forum.index'))
        ->assertOk()
        ->assertSee(route('forum.mentorship.index'), escape: false)
        ->assertSee(__('forum_mentorship.navigation.label'));

    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->actingAs($user)
        ->get(route('forum.mentorship.index'))
        ->assertOk();

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBeLessThanOrEqual(45);
});

test('livewire request identities are locked and direct thread actions authorize', function () {
    [, , $scope] = createMentorAndScope();
    $outsider = User::factory()->create();
    $mentorship = activeMentorship('livewire-authorization-request-key');
    $discovery = Livewire::actingAs($outsider)->test(MentorDiscovery::class);

    expect(fn () => $discovery->set('idempotencyKey', (string) str()->uuid()))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    Livewire::actingAs($outsider)
        ->test(MentorshipInbox::class)
        ->set('messageBody', 'An outsider cannot write to this private thread.')
        ->call('sendMessage', $mentorship->id)
        ->assertForbidden();

    expect($scope->exists)->toBeTrue();
});

test('livewire discovery rejects tampered filters without failing render', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MentorDiscovery::class)
        ->set('type', 'tampered-type')
        ->call('refreshMatches')
        ->assertHasErrors(['type']);
});

test('livewire mentorship reports require an explicit reason and truthfulness confirmation', function () {
    $mentorship = activeMentorship('livewire-report-request-key');

    $component = Livewire::actingAs($mentorship->mentee)
        ->test(MentorshipInbox::class)
        ->set('reportReason', 'harassment')
        ->set(
            'reportDetails',
            'The participant is reporting a concrete contact-safety concern.',
        )
        ->call('report', $mentorship->id)
        ->assertHasErrors(['reportTruthfulnessConfirmed']);

    expect(ForumReport::query()->count())->toBe(0);

    $component
        ->set('reportTruthfulnessConfirmed', true)
        ->call('report', $mentorship->id)
        ->assertHasNoErrors()
        ->assertSee(__('forum_mentorship.feedback.report_submitted'));

    expect(ForumReport::query()
        ->where('subject_type', ForumMentorship::class)
        ->where('subject_id', (string) $mentorship->id)
        ->where('reason', 'harassment')
        ->count())->toBe(1);
});

test('mentorship translations have complete key parity in every supported locale', function () {
    $keys = [];

    foreach (config('platform.supported_locales') as $locale) {
        $path = lang_path("{$locale}/forum_mentorship.php");
        $translations = require $path;
        $keys[$locale] = array_keys(Arr::dot($translations));
        sort($keys[$locale]);

        expect($translations['types'])->toHaveCount(13);
    }

    expect($keys['lt'])->toBe($keys['en'])
        ->and($keys['ru'])->toBe($keys['en']);
});
