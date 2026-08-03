<?php

declare(strict_types=1);

use App\Enums\PlaceQuestionStatus;
use App\Models\Place;
use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionAnswer;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('place question schema protects shared timelines and one official answer', function () {
    $questionIndexes = collect(Schema::getIndexes('place_questions'))->pluck('name');
    $answerIndexes = collect(Schema::getIndexes('place_question_answers'))->pluck('name');

    expect(Schema::hasColumns('place_questions', [
        'place_id',
        'author_user_id',
        'stable_key',
        'idempotency_key',
        'status',
        'answered_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('place_question_answers', [
            'place_question_id',
            'author_user_id',
            'stable_key',
            'idempotency_key',
            'answered_at',
        ]))->toBeTrue()
        ->and($questionIndexes)->toContain('place_questions_place_status_created_idx')
        ->and($questionIndexes)->toContain('place_questions_author_created_idx')
        ->and($answerIndexes)->toContain('place_question_answers_author_answered_idx');
});

test('place actions accept canonical dynamic identifiers and reject inaccessible places', function () {
    $place = Place::factory()->public()->create([
        'stable_key' => 'community-garden-'.Str::lower((string) Str::ulid()),
        'slug' => 'community-garden-'.Str::lower((string) Str::ulid()),
    ]);

    $this->post(route('actions.perform'), [
        'action' => 'toggle-place-save',
        'target' => $place->stable_key,
    ])->assertRedirect(route('places.show', [
        'place' => $place->stable_key,
        'tab' => 'overview',
    ]));

    $this->get(route('places.show', $place->stable_key))
        ->assertOk()
        ->assertViewHas('place', fn (array $presented): bool => $presented['key'] === $place->stable_key
            && $presented['saved'] === true);

    $privatePlace = Place::factory()->private()->for(User::factory(), 'owner')->create([
        'stable_key' => 'private-place-'.Str::lower((string) Str::ulid()),
    ]);

    $this->from(route('places.index'))
        ->post(route('actions.perform'), [
            'action' => 'toggle-place-save',
            'target' => $privatePlace->stable_key,
        ])
        ->assertRedirect(route('places.index'))
        ->assertSessionHasErrors('target');
});

test('malformed overlong confusable and stale place identifiers fail without mutation', function (string $target) {
    $this->from(route('places.index'))
        ->post(route('actions.perform'), [
            'action' => 'toggle-place-save',
            'target' => $target,
        ])
        ->assertRedirect(route('places.index'))
        ->assertSessionHasErrors('target');
})->with([
    'malformed' => 'community_garden',
    'overlong' => str_repeat('a', 191),
    'unicode confusable' => 'communіty-garden',
    'stale' => 'retired-community-garden',
]);

test('a member question is visible to its place manager and receives one idempotent official answer', function () {
    $manager = User::factory()->create();
    $place = Place::factory()->public()->for($manager, 'owner')->create();
    $questionKey = (string) Str::uuid();
    $questionBody = 'Is the accessible entrance open during the evening?';

    $payload = [
        'action' => 'create-place-question',
        'target' => $place->stable_key,
        'body' => $questionBody,
        'place_idempotency_key' => $questionKey,
    ];

    $this->post(route('actions.perform'), $payload)
        ->assertRedirect(route('places.show', [
            'place' => $place->stable_key,
            'tab' => 'questions',
        ]));
    $this->post(route('actions.perform'), $payload)->assertRedirect();

    $question = PlaceQuestion::query()->sole();

    expect($question->place_id)->toBe($place->id)
        ->and($question->author_user_id)->toBe($this->authenticatedUser->id)
        ->and($question->body)->toBe($questionBody)
        ->and($question->status)->toBe(PlaceQuestionStatus::Open);

    $this->actingAs($manager)
        ->get(route('places.show', [
            'place' => $place->stable_key,
            'tab' => 'questions',
        ]))
        ->assertOk()
        ->assertSee($questionBody)
        ->assertSee($this->authenticatedUser->name);

    $answerKey = (string) Str::uuid();
    $answerBody = 'Yes. Use the east gate until 21:00; the ramp remains available.';
    $answerPayload = [
        'action' => 'answer-place-question',
        'target' => $place->stable_key,
        'place_question' => $question->stable_key,
        'body' => $answerBody,
        'place_idempotency_key' => $answerKey,
    ];

    $this->post(route('actions.perform'), $answerPayload)->assertRedirect();
    $this->post(route('actions.perform'), $answerPayload)->assertRedirect();
    $this->from(route('places.show', [
        'place' => $place->stable_key,
        'tab' => 'questions',
    ]))->post(route('actions.perform'), [
        ...$answerPayload,
        'body' => 'A second official answer must not replace the first one.',
        'place_idempotency_key' => (string) Str::uuid(),
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('place_question');

    $question->refresh();
    $answer = PlaceQuestionAnswer::query()->sole();

    expect($question->status)->toBe(PlaceQuestionStatus::Answered)
        ->and($answer->place_question_id)->toBe($question->id)
        ->and($answer->author_user_id)->toBe($manager->id)
        ->and($answer->body)->toBe($answerBody);

    $this->actingAs($this->authenticatedUser)
        ->get(route('places.show', [
            'place' => $place->stable_key,
            'tab' => 'questions',
        ]))
        ->assertOk()
        ->assertSee($questionBody)
        ->assertSee($answerBody);
});

test('a non manager cannot publish an official place answer', function () {
    $manager = User::factory()->create();
    $place = Place::factory()->public()->for($manager, 'owner')->create();
    $question = PlaceQuestion::factory()->for($place)->create();

    $this->post(route('actions.perform'), [
        'action' => 'answer-place-question',
        'target' => $place->stable_key,
        'place_question' => $question->stable_key,
        'body' => 'This answer must not be accepted as an official response.',
        'place_idempotency_key' => (string) Str::uuid(),
    ])->assertForbidden();

    expect(PlaceQuestionAnswer::query()->count())->toBe(0);
});

test('an unverified member cannot submit a place question', function () {
    $place = Place::factory()->public()->create();

    $this->actingAs(User::factory()->unverified()->create())
        ->post(route('actions.perform'), [
            'action' => 'create-place-question',
            'target' => $place->stable_key,
            'body' => 'Is this entrance available after regular opening hours?',
            'place_idempotency_key' => (string) Str::uuid(),
        ])
        ->assertRedirect(route('verification.notice'));

    expect(PlaceQuestion::query()->count())->toBe(0);
});

test('place question presentation has a constant query count as the timeline grows', function () {
    $manager = User::factory()->create();
    $place = Place::factory()->public()->for($manager, 'owner')->create();
    $question = PlaceQuestion::factory()->for($place)->create();
    PlaceQuestionAnswer::factory()
        ->for($question, 'question')
        ->for($manager, 'author')
        ->create();
    $queries = [];

    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->get(route('places.show', [
        'place' => $place->stable_key,
        'tab' => 'questions',
    ]))->assertOk();
    $baseline = count($queries);

    PlaceQuestion::factory()->count(30)->for($place)->create();
    $queries = [];

    $this->get(route('places.show', [
        'place' => $place->stable_key,
        'tab' => 'questions',
    ]))->assertOk();

    expect(count($queries))->toBeLessThanOrEqual($baseline);
});
