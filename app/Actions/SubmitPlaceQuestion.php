<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceQuestionStatus;
use App\Models\Place;
use App\Models\PlaceQuestion;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class SubmitPlaceQuestion
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        Place $place,
        string $body,
        string $idempotencyKey,
    ): PlaceQuestion {
        $this->gate->forUser($actor)->authorize('askQuestion', $place);

        /** @var array{body: string, idempotency_key: string} $validated */
        $validated = validator([
            'body' => trim($body),
            'idempotency_key' => $idempotencyKey,
        ], [
            'body' => ['required', 'string', 'min:10', 'max:1200'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();

        $existing = PlaceQuestion::query()
            ->select([
                'id',
                'place_id',
                'author_user_id',
                'stable_key',
                'idempotency_key',
                'body',
                'status',
                'answered_at',
            ])
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();

        if ($existing !== null) {
            return $this->validatedReplay($existing, $actor, $place, $validated['body']);
        }

        return DB::transaction(function () use ($actor, $place, $validated): PlaceQuestion {
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

            $this->gate->forUser($actor)->authorize('askQuestion', $lockedPlace);

            $question = PlaceQuestion::query()->createOrFirst(
                ['idempotency_key' => $validated['idempotency_key']],
                [
                    'place_id' => $lockedPlace->id,
                    'author_user_id' => $actor->id,
                    'stable_key' => 'place-question-'.Str::lower((string) Str::ulid()),
                    'body' => $validated['body'],
                    'status' => PlaceQuestionStatus::Open,
                ],
            );

            if (! $question->wasRecentlyCreated) {
                return $this->validatedReplay($question, $actor, $lockedPlace, $validated['body']);
            }

            return $question;
        }, 3);
    }

    private function validatedReplay(
        PlaceQuestion $question,
        User $actor,
        Place $place,
        string $body,
    ): PlaceQuestion {
        if (
            $question->author_user_id !== $actor->id
            || $question->place_id !== $place->id
            || $question->body !== $body
        ) {
            throw ValidationException::withMessages([
                'place_idempotency_key' => __('places.validation.question_idempotency_conflict'),
            ]);
        }

        return $question;
    }
}
