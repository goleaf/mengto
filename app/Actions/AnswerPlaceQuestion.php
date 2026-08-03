<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceQuestionStatus;
use App\Models\Place;
use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionAnswer;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class AnswerPlaceQuestion
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        Place $place,
        string $questionKey,
        string $body,
        string $idempotencyKey,
    ): PlaceQuestionAnswer {
        /** @var array{body: string, idempotency_key: string} $validated */
        $validated = validator([
            'body' => trim($body),
            'idempotency_key' => $idempotencyKey,
        ], [
            'body' => ['required', 'string', 'min:10', 'max:1200'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();

        $question = PlaceQuestion::query()
            ->select([
                'id',
                'place_id',
                'author_user_id',
                'stable_key',
                'body',
                'status',
                'answered_at',
            ])
            ->where('place_id', $place->id)
            ->where('stable_key', $questionKey)
            ->first();

        if ($question === null) {
            throw ValidationException::withMessages([
                'place_question' => __('places.validation.question_unavailable'),
            ]);
        }

        $question->setRelation('place', $place);
        $this->gate->forUser($actor)->authorize('answer', $question);

        $existing = PlaceQuestionAnswer::query()
            ->select([
                'id',
                'place_question_id',
                'author_user_id',
                'stable_key',
                'idempotency_key',
                'body',
                'answered_at',
            ])
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();

        if ($existing !== null) {
            return $this->validatedReplay($existing, $actor, $question, $validated['body']);
        }

        return DB::transaction(function () use ($actor, $place, $question, $validated): PlaceQuestionAnswer {
            $lockedPlace = Place::query()
                ->select([
                    'id',
                    'owner_user_id',
                    'organization_id',
                    'stable_key',
                    'slug',
                    'name',
                    'visibility',
                    'status',
                    'archived_at',
                ])
                ->lockForUpdate()
                ->findOrFail($place->id);
            $lockedPlace->setRelation('organization', $place->organization);
            $lockedQuestion = PlaceQuestion::query()
                ->select([
                    'id',
                    'place_id',
                    'author_user_id',
                    'stable_key',
                    'body',
                    'status',
                    'answered_at',
                ])
                ->lockForUpdate()
                ->findOrFail($question->id);
            $lockedQuestion->setRelation('place', $lockedPlace);
            $this->gate->forUser($actor)->authorize('answer', $lockedQuestion);

            if ($lockedQuestion->status !== PlaceQuestionStatus::Open) {
                throw ValidationException::withMessages([
                    'place_question' => __('places.validation.question_already_answered'),
                ]);
            }

            $answeredAt = now();
            $answer = PlaceQuestionAnswer::query()->create([
                'place_question_id' => $lockedQuestion->id,
                'author_user_id' => $actor->id,
                'stable_key' => 'place-answer-'.Str::lower((string) Str::ulid()),
                'idempotency_key' => $validated['idempotency_key'],
                'body' => $validated['body'],
                'answered_at' => $answeredAt,
            ]);

            $lockedQuestion->forceFill([
                'status' => PlaceQuestionStatus::Answered,
                'answered_at' => $answeredAt,
            ])->save();

            return $answer;
        }, 3);
    }

    private function validatedReplay(
        PlaceQuestionAnswer $answer,
        User $actor,
        PlaceQuestion $question,
        string $body,
    ): PlaceQuestionAnswer {
        if (
            $answer->author_user_id !== $actor->id
            || $answer->place_question_id !== $question->id
            || $answer->body !== $body
        ) {
            throw ValidationException::withMessages([
                'place_idempotency_key' => __('places.validation.answer_idempotency_conflict'),
            ]);
        }

        return $answer;
    }
}
