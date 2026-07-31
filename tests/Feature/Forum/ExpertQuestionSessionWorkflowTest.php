<?php

declare(strict_types=1);

use App\Actions\ArchiveForumExpertSession;
use App\Actions\CorrectForumExpertSessionAnswer;
use App\Actions\CreateForumExpertSession;
use App\Actions\ModerateForumExpertSessionQuestion;
use App\Actions\PublishForumExpertSessionAnswer;
use App\Actions\SubmitForumExpertSessionQuestion;
use App\Actions\SubmitForumReport;
use App\Actions\WithdrawForumExpertSessionQuestion;
use App\Data\CreateForumExpertSessionData;
use App\Enums\CredentialStatus;
use App\Enums\ExpertProfileStatus;
use App\Enums\ForumExpertAnswerStatus;
use App\Enums\ForumExpertQuestionModerationStatus;
use App\Enums\ForumExpertQuestionStatus;
use App\Enums\ForumExpertSessionStatus;
use App\Enums\VerificationStatus;
use App\Livewire\Forum\ForumExpertSessionDirectory;
use App\Livewire\Forum\ForumExpertSessionWorkspace;
use App\Models\Credential;
use App\Models\ExpertProfile;
use App\Models\ForumExpertSession;
use App\Models\ForumExpertSessionAnswer;
use App\Models\ForumExpertSessionCorrection;
use App\Models\ForumExpertSessionHistory;
use App\Models\ForumExpertSessionQuestion;
use App\Models\ForumReport;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\ForumExpertSessionDemoSeeder;
use Database\Seeders\ForumModerationDefinitionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

/**
 * @return array{user: User, profile: ExpertProfile, credential: Credential}
 */
function qualifiedExpertSessionHost(
    string $scope = 'dog-trainer',
    string $jurisdiction = 'LT',
): array {
    $user = User::factory()->create();
    $profile = ExpertProfile::factory()->create([
        'owner_id' => $user->id,
        'owner_key' => $user->actor_key,
        'primary_type' => $scope,
        'specializations' => [$scope],
        'country' => $jurisdiction,
        'status' => ExpertProfileStatus::Published,
        'verification_status' => VerificationStatus::Verified,
        'verification_expires_at' => now()->addYear(),
    ]);
    $credential = Credential::factory()->create([
        'expert_profile_id' => $profile->id,
        'jurisdiction' => $jurisdiction,
        'scope' => [$scope],
        'status' => CredentialStatus::Verified,
        'expires_at' => now()->addYear(),
    ]);

    return compact('user', 'profile', 'credential');
}

function expertSessionCreateData(
    ExpertProfile $profile,
    string $jurisdiction = 'LT',
    ?string $idempotencyKey = null,
    string $timezone = 'UTC',
): CreateForumExpertSessionData {
    $opensAt = CarbonImmutable::now($timezone)->addDay()->startOfHour();
    $startsAt = $opensAt->addDays(2);

    return new CreateForumExpertSessionData(
        expertProfileId: $profile->id,
        professionalScope: $profile->primary_type,
        jurisdiction: $jurisdiction,
        title: 'Humane training questions for city dogs',
        summary: 'A public educational session about reward-based routines and appropriate referral boundaries.',
        locale: 'en',
        timezone: $timezone,
        questionOpensAt: $opensAt,
        questionClosesAt: $startsAt,
        startsAt: $startsAt,
        endsAt: $startsAt->addHours(2),
        idempotencyKey: $idempotencyKey ?? (string) Str::uuid(),
    );
}

test('expert session migration is additive reversible and preserves existing users', function () {
    $user = User::factory()->create();
    $migration = require database_path(
        'migrations/2026_07_31_001240_create_forum_expert_session_tables.php',
    );

    $migration->down();

    expect(Schema::hasTable('forum_expert_sessions'))->toBeFalse()
        ->and(Schema::hasTable('forum_expert_session_questions'))->toBeFalse()
        ->and(User::query()->whereKey($user->id)->exists())->toBeTrue();

    $migration->up();

    expect(Schema::hasTable('forum_expert_sessions'))->toBeTrue()
        ->and(Schema::hasTable('forum_expert_session_questions'))->toBeTrue()
        ->and(Schema::hasTable('forum_expert_session_answers'))->toBeTrue()
        ->and(Schema::hasTable('forum_expert_session_corrections'))->toBeTrue()
        ->and(Schema::hasTable('forum_expert_session_history'))->toBeTrue()
        ->and(User::query()->whereKey($user->id)->exists())->toBeTrue();
});

test('only a current independently verified owner can create a scoped session', function () {
    ['user' => $host, 'profile' => $profile, 'credential' => $credential] =
        qualifiedExpertSessionHost();
    $data = expertSessionCreateData($profile);

    $session = app(CreateForumExpertSession::class)->handle($host, $data);
    $sameSession = app(CreateForumExpertSession::class)->handle($host, $data);

    expect($sameSession->is($session))->toBeTrue()
        ->and(ForumExpertSession::query()->count())->toBe(1)
        ->and($session->professional_scope)->toBe('dog-trainer')
        ->and($session->jurisdiction)->toBe('LT')
        ->and($session->status)->toBe(ForumExpertSessionStatus::Published)
        ->and($session->disclaimer_version)->toBe('2026-07')
        ->and(ForumExpertSessionHistory::query()
            ->where('forum_expert_session_id', $session->id)
            ->where('event_type', 'created')
            ->count())->toBe(1)
        ->and($session->toArray())->not->toHaveKey('creation_idempotency_key');

    $credential->forceFill(['status' => CredentialStatus::Suspended])->save();

    expect(fn () => app(CreateForumExpertSession::class)->handle(
        $host,
        expertSessionCreateData($profile, idempotencyKey: (string) Str::uuid()),
    ))->toThrow(AuthorizationException::class);
});

test('scope jurisdiction expiry ownership and popularity cannot create professional authority', function () {
    ['user' => $host, 'profile' => $profile, 'credential' => $credential] =
        qualifiedExpertSessionHost();
    $popular = User::factory()->administrator()->create();

    expect(fn () => app(CreateForumExpertSession::class)->handle(
        $host,
        expertSessionCreateData($profile, 'US'),
    ))->toThrow(AuthorizationException::class)
        ->and(fn () => app(CreateForumExpertSession::class)->handle(
            $popular,
            expertSessionCreateData($profile),
        ))->toThrow(AuthorizationException::class);

    $credential->forceFill([
        'status' => CredentialStatus::Verified,
        'expires_at' => now()->subDay(),
    ])->save();

    expect(fn () => app(CreateForumExpertSession::class)->handle(
        $host,
        expertSessionCreateData($profile, idempotencyKey: (string) Str::uuid()),
    ))->toThrow(AuthorizationException::class);
});

test('session schedule and question window are validated and derived without cron', function () {
    ['user' => $host, 'profile' => $profile] = qualifiedExpertSessionHost();
    $data = expertSessionCreateData($profile);
    $invalid = new CreateForumExpertSessionData(
        expertProfileId: $data->expertProfileId,
        professionalScope: $data->professionalScope,
        jurisdiction: $data->jurisdiction,
        title: $data->title,
        summary: $data->summary,
        locale: $data->locale,
        timezone: $data->timezone,
        questionOpensAt: $data->questionOpensAt,
        questionClosesAt: $data->questionOpensAt->subMinute(),
        startsAt: $data->startsAt,
        endsAt: $data->endsAt,
        idempotencyKey: (string) Str::uuid(),
    );

    expect(fn () => app(CreateForumExpertSession::class)->handle($host, $invalid))
        ->toThrow(ValidationException::class);

    $session = ForumExpertSession::factory()->create([
        'question_opens_at' => now()->subMinute(),
        'question_closes_at' => now()->addMinute(),
        'starts_at' => now()->addHour(),
        'ends_at' => now()->addHours(2),
    ]);

    expect($session->phase())->toBe('questions-open')
        ->and($session->acceptsQuestions())->toBeTrue();

    $this->travel(2)->minutes();
    $session->refresh();

    expect($session->phase())->toBe('questions-closed')
        ->and($session->acceptsQuestions())->toBeFalse();
});

test('session preserves the source timezone while storing unambiguous UTC instants', function () {
    ['user' => $host, 'profile' => $profile] = qualifiedExpertSessionHost();
    $data = expertSessionCreateData($profile, timezone: 'Europe/Vilnius');

    $session = app(CreateForumExpertSession::class)->handle($host, $data);

    expect($session->timezone)->toBe('Europe/Vilnius')
        ->and($session->question_opens_at->getTimezone()->getName())->toBe('UTC')
        ->and($session->question_opens_at->timestamp)->toBe(
            $data->questionOpensAt->setTimezone('UTC')->timestamp,
        );
});

test('question queue is idempotent ordered bounded private before approval and withdrawable', function () {
    $session = ForumExpertSession::factory()->create();
    $firstMember = User::factory()->create();
    $secondMember = User::factory()->create();
    $token = (string) Str::uuid();
    $submit = app(SubmitForumExpertSessionQuestion::class);

    $first = $submit->handle($firstMember, $session, 'How should a calm routine begin?', $token);
    $same = $submit->handle($firstMember, $session, 'How should a calm routine begin?', $token);
    $second = $submit->handle(
        $secondMember,
        $session,
        'Which stress signals mean the session should stop?',
        (string) Str::uuid(),
    );

    expect($same->is($first))->toBeTrue()
        ->and($first->queue_position)->toBe(1)
        ->and($second->queue_position)->toBe(2)
        ->and($first->moderation_status)->toBe(ForumExpertQuestionModerationStatus::Pending)
        ->and($first->status)->toBe(ForumExpertQuestionStatus::Queued);

    $this->actingAs($secondMember);
    Livewire::test(ForumExpertSessionWorkspace::class, ['sessionId' => $session->id])
        ->assertDontSee($first->body)
        ->assertSee($second->body);

    app(WithdrawForumExpertSessionQuestion::class)
        ->handle($firstMember, $first);

    expect($first->refresh()->status)->toBe(ForumExpertQuestionStatus::Withdrawn)
        ->and($first->withdrawn_at)->not->toBeNull();
});

test('pending questions stay private at the policy and report boundaries', function () {
    $this->seed(ForumModerationDefinitionSeeder::class);
    $question = ForumExpertSessionQuestion::factory()->create();
    $stranger = User::factory()->create();
    $submit = app(SubmitForumReport::class);

    expect($stranger->can('view', $question))->toBeFalse()
        ->and(fn () => $submit->handle(
            reporter: $stranger,
            subject: $question,
            reasonKey: 'misinformation',
            details: 'A guessed private question identifier must not expose content.',
            truthfulnessConfirmed: true,
        ))->toThrow(AuthorizationException::class)
        ->and(ForumReport::query()->count())->toBe(0);

    $question->forceFill([
        'moderation_status' => ForumExpertQuestionModerationStatus::Approved,
    ])->save();

    expect($stranger->can('view', $question->refresh()))->toBeTrue();

    $submit->handle(
        reporter: $stranger,
        subject: $question,
        reasonKey: 'misinformation',
        details: 'An approved public question remains reportable.',
        truthfulnessConfirmed: true,
    );

    expect(ForumReport::query()->count())->toBe(1);
});

test('question submission is rate limited independently from idempotent retries', function () {
    $member = User::factory()->create();
    $action = app(SubmitForumExpertSessionQuestion::class);
    $rateLimitKey = 'forum-expert-question:'.hash('sha256', $member->actor_key);
    RateLimiter::clear($rateLimitKey);

    foreach (range(1, 10) as $number) {
        $session = ForumExpertSession::factory()->create();
        $idempotencyKey = (string) Str::uuid();
        $question = $action->handle(
            $member,
            $session,
            "Question {$number} has enough context to pass validation safely.",
            $idempotencyKey,
        );

        expect($action->handle(
            $member,
            $session,
            "Question {$number} has enough context to pass validation safely.",
            $idempotencyKey,
        )->is($question))->toBeTrue();
    }

    expect(fn () => $action->handle(
        $member,
        ForumExpertSession::factory()->create(),
        'This eleventh distinct question must be rejected by the bounded rate limit.',
        (string) Str::uuid(),
    ))->toThrow(ValidationException::class);

    RateLimiter::clear($rateLimitKey);
});

test('moderation answer state and sources are explicit constrained and never fetched', function () {
    Http::preventStrayRequests();
    $session = ForumExpertSession::factory()->create();
    $host = User::query()->findOrFail($session->created_by_user_id);
    $profile = ExpertProfile::query()->findOrFail($session->expert_profile_id);
    Credential::factory()->create([
        'expert_profile_id' => $profile->id,
        'jurisdiction' => $session->jurisdiction,
        'scope' => [$session->professional_scope],
    ]);
    $member = User::factory()->create();
    $question = app(SubmitForumExpertSessionQuestion::class)->handle(
        $member,
        $session,
        'What should an owner observe before increasing training difficulty?',
        (string) Str::uuid(),
    );

    app(ModerateForumExpertSessionQuestion::class)->handle(
        $host,
        $question,
        'approve',
        null,
        0,
    );
    app(ModerateForumExpertSessionQuestion::class)->handle(
        $host,
        $question->refresh(),
        'select',
        null,
        1,
    );
    $answer = app(PublishForumExpertSessionAnswer::class)->handle(
        $host,
        $question->refresh(),
        'Observe recovery time, voluntary engagement, and signs that the animal needs more distance.',
        [[
            'label' => 'Public welfare source',
            'url' => 'https://example.test/welfare',
        ]],
        (string) Str::uuid(),
    );

    expect($question->refresh()->status)->toBe(ForumExpertQuestionStatus::Answered)
        ->and($question->status->isUnanswered())->toBeFalse()
        ->and($answer->status)->toBe(ForumExpertAnswerStatus::Published)
        ->and($answer->source_links)->toHaveCount(1);

    expect(fn () => app(PublishForumExpertSessionAnswer::class)->handle(
        $host,
        ForumExpertSessionQuestion::factory()->approved()->create([
            'forum_expert_session_id' => $session->id,
        ]),
        'This answer has sufficient length but the source scheme is unsafe.',
        [['label' => 'Unsafe', 'url' => 'file:///etc/passwd']],
        (string) Str::uuid(),
    ))->toThrow(ValidationException::class);
});

test('answer corrections preserve immutable previous versions and reject stale edits', function () {
    $answer = ForumExpertSessionAnswer::factory()->create();
    $host = User::query()->findOrFail($answer->author_user_id);
    $session = ForumExpertSession::query()->findOrFail($answer->forum_expert_session_id);
    $profile = ExpertProfile::query()->findOrFail($session->expert_profile_id);
    Credential::factory()->create([
        'expert_profile_id' => $profile->id,
        'jurisdiction' => $session->jurisdiction,
        'scope' => [$session->professional_scope],
    ]);
    $previousBody = $answer->body;

    $correction = app(CorrectForumExpertSessionAnswer::class)->handle(
        $host,
        $answer,
        'The corrected answer makes the educational scope and referral boundary explicit.',
        [['label' => 'Updated source', 'url' => 'https://example.test/updated']],
        'Clarified the safety boundary.',
        1,
    );

    expect($correction->previous_body)->toBe($previousBody)
        ->and($correction->version)->toBe(2)
        ->and($answer->refresh()->current_version)->toBe(2)
        ->and($answer->status)->toBe(ForumExpertAnswerStatus::Corrected)
        ->and(ForumExpertSessionCorrection::query()->count())->toBe(1);

    expect(fn () => app(CorrectForumExpertSessionAnswer::class)->handle(
        $host,
        $answer,
        'Another corrected answer with sufficient content for validation.',
        [],
        'A stale change.',
        1,
    ))->toThrow(ValidationException::class)
        ->and(ForumExpertSessionCorrection::query()->count())->toBe(1);
});

test('an administrator may publish a correction but an unrelated member may not', function () {
    $answer = ForumExpertSessionAnswer::factory()->create();
    $administrator = User::factory()->administrator()->create();
    $member = User::factory()->create();

    expect($administrator->can('correct', $answer))->toBeTrue()
        ->and($member->can('correct', $answer))->toBeFalse();

    $correction = app(CorrectForumExpertSessionAnswer::class)->handle(
        $administrator,
        $answer,
        'An administrator correction keeps the public educational boundary explicit.',
        [],
        'Corrected a material safety statement.',
        1,
    );

    expect($correction->actor_user_id)->toBe($administrator->id)
        ->and($answer->refresh()->current_version)->toBe(2);
});

test('expert session correction and history records are append only', function () {
    $correction = ForumExpertSessionCorrection::factory()->create();
    $history = ForumExpertSessionHistory::factory()->create();

    expect(fn () => $correction->forceFill(['reason' => 'Rewritten'])->save())
        ->toThrow(LogicException::class)
        ->and(fn () => $correction->delete())->toThrow(LogicException::class)
        ->and(fn () => $history->forceFill(['reason_code' => 'rewritten'])->save())
        ->toThrow(LogicException::class)
        ->and(fn () => $history->delete())->toThrow(LogicException::class);
});

test('archive preserves questions answers corrections reports and history', function () {
    $answer = ForumExpertSessionAnswer::factory()->create();
    $session = ForumExpertSession::query()->findOrFail($answer->forum_expert_session_id);
    $host = User::query()->findOrFail($session->created_by_user_id);
    $profile = ExpertProfile::query()->findOrFail($session->expert_profile_id);
    Credential::factory()->create([
        'expert_profile_id' => $profile->id,
        'jurisdiction' => $session->jurisdiction,
        'scope' => [$session->professional_scope],
    ]);
    ForumExpertSessionCorrection::factory()->create([
        'forum_expert_session_id' => $session->id,
        'forum_expert_session_answer_id' => $answer->id,
        'actor_user_id' => $host->id,
    ]);

    app(ArchiveForumExpertSession::class)->handle(
        $host,
        $session,
        'host-archived',
        0,
    );

    expect($session->refresh()->status)->toBe(ForumExpertSessionStatus::Archived)
        ->and($session->archived_at)->not->toBeNull()
        ->and($session->questions()->count())->toBe(1)
        ->and($session->answers()->count())->toBe(1)
        ->and(ForumExpertSessionCorrection::query()
            ->where('forum_expert_session_id', $session->id)
            ->count())->toBe(1)
        ->and($session->history()->where('event_type', 'archived')->count())->toBe(1);
});

test('session question and answer use the unified private report pipeline', function () {
    $this->seed(ForumModerationDefinitionSeeder::class);
    $answer = ForumExpertSessionAnswer::factory()->create();
    $question = $answer->question;
    $session = $answer->session;
    $reporter = User::factory()->create();
    $submit = app(SubmitForumReport::class);

    foreach ([$session, $question, $answer] as $subject) {
        $submit->handle(
            reporter: $reporter,
            subject: $subject,
            reasonKey: 'misinformation',
            details: 'The public claim needs moderation review.',
            truthfulnessConfirmed: true,
        );
    }

    expect(ForumReport::query()->count())->toBe(3)
        ->and(ForumReport::query()->where('reporter_id', $reporter->id)->count())->toBe(3)
        ->and(ForumReport::query()->get()->every(
            fn (ForumReport $report): bool => ! array_key_exists(
                'reporter_id',
                $report->toArray(),
            ),
        ))->toBeTrue();
});

test('livewire actions authorize direct calls and render localized disclaimers', function () {
    $session = ForumExpertSession::factory()->create();
    $member = User::factory()->russian()->create();
    $blocked = User::factory()->blocked()->create();

    $this->actingAs($member);
    Livewire::test(ForumExpertSessionWorkspace::class, ['sessionId' => $session->id])
        ->assertSee(__('forum_expert_sessions.disclaimers.2026-07'))
        ->set('questionForm.body', 'Какие признаки стресса требуют немедленно остановить упражнение?')
        ->set('questionForm.idempotencyKey', (string) Str::uuid())
        ->call('submitQuestion')
        ->assertHasNoErrors();

    $this->actingAs($blocked);
    Livewire::test(ForumExpertSessionWorkspace::class, ['sessionId' => $session->id])
        ->set('questionForm.body', 'This direct action must not bypass account restrictions.')
        ->set('questionForm.idempotencyKey', (string) Str::uuid())
        ->call('submitQuestion')
        ->assertForbidden();

    $this->actingAs($member);
    Livewire::test(ForumExpertSessionDirectory::class)
        ->assertSee(__('forum_expert_sessions.page.heading'));
});

test('livewire rejects foreign queue identifiers and validates destructive moderation reasons', function () {
    $session = ForumExpertSession::factory()->create();
    $host = User::query()->findOrFail($session->created_by_user_id);
    $profile = ExpertProfile::query()->findOrFail($session->expert_profile_id);
    Credential::factory()->create([
        'expert_profile_id' => $profile->id,
        'jurisdiction' => $session->jurisdiction,
        'scope' => [$session->professional_scope],
    ]);
    $question = ForumExpertSessionQuestion::factory()->create([
        'forum_expert_session_id' => $session->id,
    ]);
    $foreignQuestion = ForumExpertSessionQuestion::factory()->create();

    expect(fn () => Livewire::actingAs($host)
        ->test(ForumExpertSessionWorkspace::class, ['sessionId' => $session->id])
        ->call('prepareModeration', $foreignQuestion->id))
        ->toThrow(ModelNotFoundException::class);

    Livewire::actingAs($host)
        ->test(ForumExpertSessionWorkspace::class, ['sessionId' => $session->id])
        ->call('prepareModeration', $question->id)
        ->set('moderationForm.decision', 'remove')
        ->set('moderationForm.reason', '')
        ->call('moderate')
        ->assertHasErrors(['moderationForm.reason' => 'required']);
});

test('expert session routes render the directory and public workspace', function () {
    $session = ForumExpertSession::factory()->create();

    $this->get(route('forum.index'))
        ->assertOk()
        ->assertSee(__('forum_expert_sessions.navigation.label'));
    $this->get(route('forum.expert-sessions.index'))
        ->assertOk()
        ->assertSee(__('forum_expert_sessions.page.heading'));
    $this->get(route('forum.expert-sessions.show', $session))
        ->assertOk()
        ->assertSee($session->title)
        ->assertSee(__('forum_expert_sessions.disclaimers.2026-07'))
        ->assertDontSee('forum_expert_sessions.answer.heading')
        ->assertDontSee('forum_expert_sessions.answer.sources');
});

test('expert session workspace query count stays bounded as the queue grows', function () {
    $session = ForumExpertSession::factory()->create();
    $host = User::query()->findOrFail($session->created_by_user_id);
    $profile = ExpertProfile::query()->findOrFail($session->expert_profile_id);
    Credential::factory()->create([
        'expert_profile_id' => $profile->id,
        'jurisdiction' => $session->jurisdiction,
        'scope' => [$session->professional_scope],
    ]);
    $firstQuestion = ForumExpertSessionQuestion::factory()->approved()->create([
        'forum_expert_session_id' => $session->id,
        'status' => ForumExpertQuestionStatus::Answered,
    ]);
    ForumExpertSessionAnswer::factory()->create([
        'forum_expert_session_id' => $session->id,
        'forum_expert_session_question_id' => $firstQuestion->id,
        'author_user_id' => $host->id,
    ]);

    $renderQueryCount = static function () use ($host, $session): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::actingAs($host)
            ->test(ForumExpertSessionWorkspace::class, ['sessionId' => $session->id])
            ->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $queryCount;
    };
    $singleQuestionQueries = $renderQueryCount();

    foreach (range(1, 11) as $position) {
        $question = ForumExpertSessionQuestion::factory()->approved()->create([
            'forum_expert_session_id' => $session->id,
            'queue_position' => $position + 1,
            'status' => ForumExpertQuestionStatus::Answered,
        ]);
        ForumExpertSessionAnswer::factory()->create([
            'forum_expert_session_id' => $session->id,
            'forum_expert_session_question_id' => $question->id,
            'author_user_id' => $host->id,
        ]);
    }
    $twelveQuestionQueries = $renderQueryCount();

    expect($singleQuestionQueries)->toBeLessThanOrEqual(18)
        ->and($twelveQuestionQueries)->toBeLessThanOrEqual($singleQuestionQueries + 1);
});

test('all expert session factories states seed reruns and translation catalogues are valid', function () {
    ForumExpertSession::factory()->upcoming()->create();
    ForumExpertSession::factory()->ended()->create();
    ForumExpertSession::factory()->archived()->create();
    ForumExpertSessionQuestion::factory()->approved()->create();
    ForumExpertSessionQuestion::factory()->selected()->create();
    ForumExpertSessionQuestion::factory()->declined()->create();
    ForumExpertSessionAnswer::factory()->corrected()->create();
    ForumExpertSessionCorrection::factory()->create();
    ForumExpertSessionHistory::factory()->create();

    foreach (['en', 'lt', 'ru'] as $locale) {
        $catalogue = require lang_path($locale.'/forum_expert_sessions.php');
        expect(array_keys($catalogue))->toBe(array_keys(
            require lang_path('en/forum_expert_sessions.php'),
        ));
    }

    User::factory()->create([
        'actor_key' => 'demo-lithuanian',
        'name' => 'Demo Lithuanian Member',
        'email' => 'lithuanian@example.test',
    ]);

    $this->seed(ForumExpertSessionDemoSeeder::class);
    $sessionId = ForumExpertSession::query()
        ->where('stable_key', 'demo-dog-training-question-session')
        ->value('id');
    $credentialId = Credential::query()
        ->where('credential_identifier_hash', hash(
            'sha256',
            'demo-lithuanian-animal-trainer-credential',
        ))
        ->value('id');
    $this->seed(ForumExpertSessionDemoSeeder::class);

    expect(ForumExpertSession::query()
        ->where('stable_key', 'demo-dog-training-question-session')
        ->count())->toBe(1)
        ->and(ForumExpertSession::query()
            ->where('stable_key', 'demo-dog-training-question-session')
            ->value('id'))->toBe($sessionId)
        ->and(ForumExpertSessionQuestion::query()
            ->where('stable_key', 'demo-dog-training-question')
            ->count())->toBe(1)
        ->and(ForumExpertSessionAnswer::query()
            ->where('stable_key', 'demo-dog-training-answer')
            ->count())->toBe(1)
        ->and(Credential::query()
            ->where('credential_identifier_hash', hash(
                'sha256',
                'demo-lithuanian-animal-trainer-credential',
            ))
            ->count())->toBe(1)
        ->and(Credential::query()
            ->where('credential_identifier_hash', hash(
                'sha256',
                'demo-lithuanian-animal-trainer-credential',
            ))
            ->value('id'))->toBe($credentialId);
});
