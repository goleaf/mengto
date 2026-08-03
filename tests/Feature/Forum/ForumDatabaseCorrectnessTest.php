<?php

declare(strict_types=1);

use App\Actions\AcceptForumAnswer;
use App\Actions\CloseForumModerationCase;
use App\Actions\RecordAnswerVote;
use App\Enums\AdoptionCaseStatus;
use App\Enums\ForumVoteValue;
use App\Enums\PhotoReactionType;
use App\Models\AdoptionCase;
use App\Models\ForumAnswer;
use App\Models\ForumModerationCase;
use App\Models\ForumReport;
use App\Models\ForumReportEvent;
use App\Models\ForumReportReason;
use App\Models\ForumTopic;
use App\Models\ForumTopicAcceptance;
use App\Models\ForumVote;
use App\Models\PhotoAsset;
use App\Models\PhotoReaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

test('selected forum tables expose their foreign unique and compound controls', function () {
    $voteIndexes = collect(Schema::getIndexes('forum_votes'))->pluck('name');
    $acceptanceIndexes = collect(Schema::getIndexes('forum_topic_acceptances'))->pluck('name');
    $reactionIndexes = collect(Schema::getIndexes('photo_reactions'))->pluck('name');
    $caseIndexes = collect(Schema::getIndexes('forum_moderation_cases'))->pluck('name');

    expect($voteIndexes)
        ->toContain('forum_votes_answer_user_unique')
        ->toContain('forum_votes_answer_value_idx')
        ->and($acceptanceIndexes)
        ->toContain('forum_topic_acceptances_subject_type_unique')
        ->toContain('forum_topic_acceptances_active_idx')
        ->and($reactionIndexes)
        ->toContain('photo_reactions_photo_asset_id_user_id_unique')
        ->toContain('photo_reactions_photo_asset_id_reaction_index')
        ->and($caseIndexes)
        ->toContain('forum_moderation_cases_case_number_unique')
        ->toContain('forum_moderation_cases_queue_idx')
        ->toContain('forum_moderation_cases_subject_status_idx')
        ->toContain('forum_moderation_cases_closure_idempotency_key_unique');

    $expectedForeignKeys = [
        'forum_votes' => ['answer_id', 'user_id', 'reputation_event_id'],
        'forum_topic_acceptances' => [
            'forum_topic_id',
            'forum_answer_id',
            'accepted_by_user_id',
        ],
        'photo_reactions' => ['photo_asset_id', 'user_id'],
        'forum_moderation_cases' => ['assigned_to_user_id', 'opened_by_user_id'],
    ];
    $foreignKeyColumns = [
        'forum_votes' => collect(Schema::getForeignKeys('forum_votes')),
        'forum_topic_acceptances' => collect(
            Schema::getForeignKeys('forum_topic_acceptances'),
        ),
        'photo_reactions' => collect(Schema::getForeignKeys('photo_reactions')),
        'forum_moderation_cases' => collect(
            Schema::getForeignKeys('forum_moderation_cases'),
        ),
    ];

    $foreignKeyColumns = collect($foreignKeyColumns)->map(
        static fn ($foreignKeys): array => $foreignKeys
            ->flatMap(static fn (array $foreignKey): array => $foreignKey['columns'])
            ->all(),
    );

    foreach ($expectedForeignKeys as $table => $columns) {
        expect($foreignKeyColumns->get($table))->toContain(...$columns);
    }

    expect(Schema::hasColumns('forum_moderation_cases', [
        'lock_version',
        'closure_idempotency_key',
    ]))->toBeTrue();
});

test('vote and reaction values are enums and invalid raw values are rejected by the database', function () {
    $answer = ForumAnswer::factory()->create();
    $asset = PhotoAsset::factory()->create();
    $user = User::factory()->create();
    $vote = ForumVote::factory()->create([
        'answer_id' => $answer->id,
        'user_key' => $user->actor_key,
        'value' => ForumVoteValue::Helpful,
    ]);
    $reaction = PhotoReaction::factory()->create([
        'photo_asset_id' => $asset->id,
        'user_id' => $user->id,
        'reaction' => PhotoReactionType::Support,
    ]);

    expect($vote->value)->toBe(ForumVoteValue::Helpful)
        ->and($reaction->reaction)->toBe(PhotoReactionType::Support);

    $invalidVote = new ForumVote;
    $invalidVote->setRawAttributes([
        'answer_id' => $answer->id,
        'user_key' => 'invalid-vote-value',
        'value' => 'not-a-supported-vote',
        'effect_revision' => 0,
    ]);

    expect(fn () => $invalidVote->save())->toThrow(QueryException::class);

    $invalidReaction = new PhotoReaction;
    $invalidReaction->setRawAttributes([
        'photo_asset_id' => $asset->id,
        'user_id' => User::factory()->create()->id,
        'reaction' => 'not-a-supported-reaction',
    ]);

    expect(fn () => $invalidReaction->save())->toThrow(QueryException::class);
});

test('database uniqueness prevents duplicate votes and photo reactions', function () {
    $answer = ForumAnswer::factory()->create();
    $asset = PhotoAsset::factory()->create();
    $user = User::factory()->create();

    ForumVote::factory()->create([
        'answer_id' => $answer->id,
        'user_id' => $user->id,
        'user_key' => $user->actor_key,
        'value' => ForumVoteValue::Helpful,
    ]);

    expect(fn () => ForumVote::factory()->create([
        'answer_id' => $answer->id,
        'user_id' => $user->id,
        'user_key' => $user->actor_key,
        'value' => ForumVoteValue::Outdated,
    ]))->toThrow(QueryException::class);

    PhotoReaction::factory()->create([
        'photo_asset_id' => $asset->id,
        'user_id' => $user->id,
        'reaction' => PhotoReactionType::Like,
    ]);

    expect(fn () => PhotoReaction::factory()->create([
        'photo_asset_id' => $asset->id,
        'user_id' => $user->id,
        'reaction' => PhotoReactionType::Love,
    ]))->toThrow(QueryException::class);

    expect(ForumVote::query()->where('answer_id', $answer->id)->count())->toBe(1)
        ->and(PhotoReaction::query()->where('photo_asset_id', $asset->id)->count())->toBe(1);
});

test('replayed answer voting keeps one vote and one helpful aggregate', function () {
    $actor = User::factory()->create();
    $answer = ForumAnswer::factory()->create();
    $this->actingAs($actor);

    $record = app(RecordAnswerVote::class);
    $record->handle($answer->id, 'helpful', 'Clear and practical.');
    $record->handle($answer->id, 'helpful', 'Still clear and practical.');

    expect(ForumVote::query()
        ->where('answer_id', $answer->id)
        ->where('user_key', $actor->actor_key)
        ->count())->toBe(1)
        ->and($answer->refresh()->helpful_count)->toBe(1)
        ->and($answer->votes()->firstOrFail()->reason)
        ->toBe('Still clear and practical.');
});

test('representative lifecycle records cast json versions enums and immutable timestamps', function () {
    $topic = ForumTopic::factory()->archived()->create([
        'structured_data' => ['allows_multiple_accepted_answers' => false],
    ]);
    $answer = ForumAnswer::factory()->create(['topic_id' => $topic->id]);
    $acceptance = ForumTopicAcceptance::factory()->forAnswer($answer)->create([
        'metadata' => ['source' => 'database-correctness-contract'],
    ]);
    $case = ForumModerationCase::factory()->create([
        'status' => 'closed',
        'resolved_at' => now()->subMinute(),
        'closed_at' => now(),
        'metadata' => ['retention' => 'preserve'],
    ]);
    $adoption = AdoptionCase::factory()->closed()->create();

    expect($topic->structured_data)->toBe([
        'allows_multiple_accepted_answers' => false,
    ])
        ->and($topic->structured_data_version)->toBeInt()
        ->and($topic->archived_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($acceptance->metadata)->toBe([
            'source' => 'database-correctness-contract',
        ])
        ->and($acceptance->accepted_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($case->metadata)->toBe(['retention' => 'preserve'])
        ->and($case->resolved_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($case->closed_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($case->lock_version)->toBe(0)
        ->and($adoption->status)->toBe(AdoptionCaseStatus::Closed)
        ->and($adoption->lock_version)->toBeInt()
        ->and($adoption->closed_at)->toBeInstanceOf(CarbonImmutable::class);
});

test('a foreign key rejects a vote for an answer that does not exist', function () {
    $vote = new ForumVote;
    $vote->setRawAttributes([
        'answer_id' => PHP_INT_MAX,
        'user_key' => 'missing-answer-vote',
        'value' => 'helpful',
        'effect_revision' => 0,
    ]);

    expect(fn () => $vote->save())->toThrow(QueryException::class);
});

test('competing answer acceptance leaves one canonical accepted answer', function () {
    $actor = User::factory()->administrator()->create();
    $topic = ForumTopic::factory()->create([
        'author_id' => $actor->id,
        'author_key' => $actor->actor_key,
        'structured_data' => ['allows_multiple_accepted_answers' => false],
    ]);
    $first = ForumAnswer::factory()->create(['topic_id' => $topic->id]);
    $second = ForumAnswer::factory()->create(['topic_id' => $topic->id]);
    $this->actingAs($actor);

    $accept = app(AcceptForumAnswer::class);
    $accept->handle($first->id);
    $accept->handle($second->id);

    expect(ForumTopicAcceptance::query()
        ->where('forum_topic_id', $topic->id)
        ->where('is_active', true)
        ->count())->toBe(1)
        ->and($first->refresh()->is_accepted)->toBeFalse()
        ->and($second->refresh()->is_accepted)->toBeTrue()
        ->and($topic->refresh()->accepted_answer_id)->toBe($second->id);
});

test('moderation case closure is authorized versioned and idempotent', function () {
    $actor = User::factory()->administrator()->create();
    $report = ForumReport::factory()->create();
    $case = ForumModerationCase::factory()->create([
        'opened_by_user_id' => $actor->id,
        'status' => 'actioned',
        'resolved_at' => now(),
    ]);
    $case->reports()->attach($report->id, ['linked_by_user_id' => $actor->id]);
    $close = app(CloseForumModerationCase::class);
    $idempotencyKey = 'moderation-case-close-'.fake()->uuid();

    $closed = $close->handle($actor, $case, 0, $idempotencyKey);
    $replayed = $close->handle($actor, $case, 0, $idempotencyKey);

    expect($closed->id)->toBe($case->id)
        ->and($closed->status)->toBe('closed')
        ->and($closed->closed_at)->not->toBeNull()
        ->and($closed->lock_version)->toBe(1)
        ->and($replayed->id)->toBe($closed->id)
        ->and($replayed->lock_version)->toBe(1)
        ->and(ForumReportEvent::query()
            ->where('forum_report_id', $report->id)
            ->where('event_type', 'moderation-case-closed')
            ->count())->toBe(1);

    expect(fn () => $close->handle(
        $actor,
        $case,
        0,
        'moderation-case-close-'.fake()->uuid(),
    ))->toThrow(ValidationException::class);

    expect(ForumReportEvent::query()
        ->where('forum_report_id', $report->id)
        ->where('event_type', 'moderation-case-closed')
        ->count())->toBe(1);
});

test('moderation case closure rolls back every write when its bounded report limit fails', function () {
    $actor = User::factory()->administrator()->create();
    $topic = ForumTopic::factory()->create();
    $reason = ForumReportReason::factory()->create();
    $case = ForumModerationCase::factory()->create([
        'opened_by_user_id' => $actor->id,
        'status' => 'actioned',
        'resolved_at' => now(),
    ]);
    $reports = ForumReport::factory()->count(101)->create([
        'topic_id' => $topic->id,
        'subject_type' => ForumTopic::class,
        'subject_id' => (string) $topic->id,
        'reporter_id' => $actor->id,
        'forum_report_reason_id' => $reason->id,
    ]);

    $case->reports()->attach(
        $reports->mapWithKeys(
            static fn (ForumReport $report): array => [
                $report->id => ['linked_by_user_id' => $actor->id],
            ],
        )->all(),
    );

    expect(fn () => app(CloseForumModerationCase::class)->handle(
        $actor,
        $case,
        0,
        'moderation-case-close-'.fake()->uuid(),
    ))->toThrow(ValidationException::class);

    expect($case->refresh()->status)->toBe('actioned')
        ->and($case->closed_at)->toBeNull()
        ->and($case->lock_version)->toBe(0)
        ->and($case->closure_idempotency_key)->toBeNull()
        ->and(ForumReportEvent::query()
            ->where('event_type', 'moderation-case-closed')
            ->count())->toBe(0);
});

test('moderation case closure query count stays constant as linked reports grow', function () {
    $actor = User::factory()->administrator()->create();
    $topic = ForumTopic::factory()->create();
    $reason = ForumReportReason::factory()->create();
    $queryCount = function (int $reportCount) use ($actor, $reason, $topic): int {
        $case = ForumModerationCase::factory()->create([
            'opened_by_user_id' => $actor->id,
            'status' => 'actioned',
            'resolved_at' => now(),
        ]);
        $reports = ForumReport::factory()->count($reportCount)->create([
            'topic_id' => $topic->id,
            'subject_type' => ForumTopic::class,
            'subject_id' => (string) $topic->id,
            'reporter_id' => $actor->id,
            'forum_report_reason_id' => $reason->id,
        ]);
        $case->reports()->attach(
            $reports->mapWithKeys(
                static fn (ForumReport $report): array => [
                    $report->id => ['linked_by_user_id' => $actor->id],
                ],
            )->all(),
        );

        DB::flushQueryLog();
        DB::enableQueryLog();

        app(CloseForumModerationCase::class)->handle(
            $actor,
            $case,
            0,
            'moderation-case-close-'.fake()->uuid(),
        );

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    expect($queryCount(20))->toBe($queryCount(1));
});

test('a non administrator cannot close a moderation case', function () {
    $actor = User::factory()->create();
    $case = ForumModerationCase::factory()->create([
        'status' => 'actioned',
        'resolved_at' => now(),
    ]);

    expect(fn () => app(CloseForumModerationCase::class)->handle(
        $actor,
        $case,
        0,
        'moderation-case-close-'.fake()->uuid(),
    ))->toThrow(AuthorizationException::class);
});

test('the reconciliation migration preserves populated rows across rollback and reapply', function () {
    $answer = ForumAnswer::factory()->create();
    $asset = PhotoAsset::factory()->create();
    $user = User::factory()->create();
    $vote = ForumVote::factory()->create([
        'answer_id' => $answer->id,
        'user_key' => $user->actor_key,
        'value' => ForumVoteValue::NeedsSource,
    ]);
    $reaction = PhotoReaction::factory()->create([
        'photo_asset_id' => $asset->id,
        'user_id' => $user->id,
        'reaction' => PhotoReactionType::Useful,
    ]);
    $case = ForumModerationCase::factory()->create();
    $migration = require database_path(
        'migrations/2026_08_03_120000_reconcile_forum_database_correctness.php',
    );

    $migration->down();

    expect(Schema::hasColumn('forum_moderation_cases', 'lock_version'))->toBeFalse()
        ->and(Schema::hasColumn(
            'forum_moderation_cases',
            'closure_idempotency_key',
        ))->toBeFalse()
        ->and(ForumVote::query()->whereKey($vote->id)->value('value'))
        ->toBe(ForumVoteValue::NeedsSource)
        ->and(PhotoReaction::query()->whereKey($reaction->id)->value('reaction'))
        ->toBe(PhotoReactionType::Useful)
        ->and(ForumModerationCase::query()->whereKey($case->id)->exists())->toBeTrue();

    $migration->up();

    expect(ForumVote::query()->whereKey($vote->id)->value('value'))
        ->toBe(ForumVoteValue::NeedsSource)
        ->and(PhotoReaction::query()->whereKey($reaction->id)->value('reaction'))
        ->toBe(PhotoReactionType::Useful)
        ->and(ForumModerationCase::query()->findOrFail($case->id)->lock_version)->toBe(0);
});
