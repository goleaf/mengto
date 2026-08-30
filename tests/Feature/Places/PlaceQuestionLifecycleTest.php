<?php

declare(strict_types=1);

use App\Actions\AnswerPlaceQuestion;
use App\Actions\AssignForumModerationCase;
use App\Actions\ClosePlaceQuestion;
use App\Actions\ModeratePlaceQuestion;
use App\Actions\OpenForumModerationCase;
use App\Actions\ReopenPlaceQuestion;
use App\Actions\SubmitPlaceQuestion;
use App\Actions\SubmitForumReport;
use App\Actions\UpdatePlaceQuestionAnswer;
use App\Enums\PlaceQuestionStatus;
use App\Models\ForumNotification;
use App\Models\ForumReportEvent;
use App\Models\Place;
use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionAnswerVersion;
use App\Models\PlaceQuestionEvent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Database\Seeders\ForumModerationDefinitionSeeder;

test('place questions remain visible across accounts and official answers retain versions', function (): void {
    $author = User::factory()->create();
    $reader = User::factory()->create();
    $manager = User::factory()->create();
    $place = Place::factory()->public()->for($manager, 'owner')->create();

    $question = app(SubmitPlaceQuestion::class)->handle(
        $author,
        $place,
        'Is the quiet entrance available after 18:00?',
        (string) Str::uuid(),
    );

    expect(PlaceQuestion::query()->visible()->whereKey($question)->exists())->toBeTrue();
    $this->actingAs($reader)
        ->get(route('places.show', ['place' => $place->stable_key, 'tab' => 'questions']))
        ->assertOk()
        ->assertSee('Is the quiet entrance available after 18:00?');

    $answer = app(AnswerPlaceQuestion::class)->handle(
        $manager,
        $place,
        $question->stable_key,
        'Yes. The east-side entrance remains open until 21:00.',
        (string) Str::uuid(),
    );
    $updated = app(UpdatePlaceQuestionAnswer::class)->handle(
        $manager,
        $answer,
        'Yes. The east-side entrance remains open until 22:00.',
        'Closing time changed after the seasonal schedule update.',
        (string) Str::uuid(),
    );

    expect($updated->current_version)->toBe(2)
        ->and(PlaceQuestionAnswerVersion::query()->where('place_question_answer_id', $answer->id)->count())->toBe(2)
        ->and(PlaceQuestionEvent::query()->where('place_question_id', $question->id)->count())->toBeGreaterThanOrEqual(3);
});

test('question authors and managers can close and reopen while unrelated users cannot', function (): void {
    $author = User::factory()->create();
    $manager = User::factory()->create();
    $outsider = User::factory()->create();
    $place = Place::factory()->public()->for($manager, 'owner')->create();
    $question = app(SubmitPlaceQuestion::class)->handle(
        $author,
        $place,
        'Can pets wait safely near the reception desk?',
        (string) Str::uuid(),
    );

    expect(fn () => app(ClosePlaceQuestion::class)->handle(
        $outsider,
        $question,
        'This question has been resolved.',
        (string) Str::uuid(),
    ))->toThrow(AuthorizationException::class);

    $closed = app(ClosePlaceQuestion::class)->handle(
        $author,
        $question,
        'The manager answered this during our visit.',
        (string) Str::uuid(),
    );
    expect($closed->status)->toBe(PlaceQuestionStatus::Closed)
        ->and($closed->closed_at)->not->toBeNull();

    $reopened = app(ReopenPlaceQuestion::class)->handle(
        $manager,
        $closed,
        'Opening hours changed, so a current answer is needed.',
        (string) Str::uuid(),
    );
    expect($reopened->status)->toBe(PlaceQuestionStatus::Open)
        ->and($reopened->closed_at)->toBeNull();
});

test('moderation hides a question without destroying its history and emits deduplicated notifications', function (): void {
    $author = User::factory()->create();
    $administrator = User::factory()->create(['is_admin' => true]);
    $place = Place::factory()->public()->create();
    $question = app(SubmitPlaceQuestion::class)->handle(
        $author,
        $place,
        'Does this location permit pets in the waiting area?',
        (string) Str::uuid(),
    );
    $idempotencyKey = (string) Str::uuid();

    $first = app(ModeratePlaceQuestion::class)->handle(
        $administrator,
        $question,
        'hidden',
        'The question contains private contact details.',
        $idempotencyKey,
    );
    $second = app(ModeratePlaceQuestion::class)->handle(
        $administrator,
        $question->refresh(),
        'hidden',
        'The question contains private contact details.',
        $idempotencyKey,
    );

    expect($second->is($first))->toBeTrue()
        ->and(PlaceQuestion::query()->visible()->whereKey($question)->exists())->toBeFalse()
        ->and(PlaceQuestionEvent::query()->where('place_question_id', $question->id)->where('event_type', 'moderated')->count())->toBe(1)
        ->and(ForumNotification::query()->where('user_key', $author->actor_key)->count())->toBeLessThanOrEqual(1);
});

test('question reports enter the canonical private moderation case and assignment history', function (): void {
    $this->seed(ForumModerationDefinitionSeeder::class);
    $author = User::factory()->create();
    $reporter = User::factory()->create();
    $moderator = User::factory()->administrator()->create();
    $place = Place::factory()->public()->create();
    $question = app(SubmitPlaceQuestion::class)->handle(
        $author,
        $place,
        'Is this emergency access information still accurate?',
        (string) Str::uuid(),
    );

    $report = app(SubmitForumReport::class)->handle(
        $reporter,
        $question,
        'outdated-critical-information',
        'The published access information changed after the latest safety inspection.',
        true,
        idempotencyKey: (string) Str::uuid(),
    );
    $case = app(OpenForumModerationCase::class)->handle($moderator, $report);
    app(AssignForumModerationCase::class)->handle($moderator, $case, $moderator);

    expect($report->toArray())->not->toHaveKeys(['reporter_id', 'reporter_key', 'details'])
        ->and($case->subject_type)->toBe(PlaceQuestion::class)
        ->and($case->subject_id)->toBe((string) $question->id)
        ->and($case->refresh()->assigned_to_user_id)->toBe($moderator->id)
        ->and(ForumReportEvent::query()->where('forum_report_id', $report->id)->count())->toBe(3);
});
