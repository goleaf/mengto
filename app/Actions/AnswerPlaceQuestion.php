<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceQuestionStatus;
use App\Models\Place;
use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionAnswer;
use App\Models\PlaceQuestionAnswerVersion;
use App\Models\PlaceQuestionEvent;
use App\Models\User;
use App\Services\PlaceContributionNotifier;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class AnswerPlaceQuestion
{
    public function __construct(
        private Gate $gate,
        private PlaceContributionNotifier $notifier,
    ) {}

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
                'moderation_status',
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
                ->with('organization')
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
            $lockedQuestion = PlaceQuestion::query()
                ->select([
                    'id',
                    'place_id',
                    'author_user_id',
                    'stable_key',
                    'body',
                    'status',
                    'moderation_status',
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
                'current_version' => 1,
                'answered_at' => $answeredAt,
            ]);

            PlaceQuestionAnswerVersion::query()->create([
                'place_question_answer_id' => $answer->id,
                'editor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'version' => 1,
                'body' => $validated['body'],
                'created_at' => $answeredAt,
            ]);

            $lockedQuestion->forceFill([
                'status' => PlaceQuestionStatus::Answered,
                'answered_at' => $answeredAt,
            ])->save();

            PlaceQuestionEvent::query()->create([
                'place_question_id' => $lockedQuestion->id,
                'actor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'event_type' => 'answered',
                'from_status' => PlaceQuestionStatus::Open->value,
                'to_status' => PlaceQuestionStatus::Answered->value,
                'public_summary_key' => 'places.questions.events.answered',
                'created_at' => $answeredAt,
            ]);

            $recipient = User::query()->find($lockedQuestion->author_user_id);
            if ($recipient !== null && $recipient->id !== $actor->id) {
                DB::afterCommit(fn () => $this->notifier->send(
                    $recipient,
                    'place_question_answered',
                    'places.notifications.question_answered_title',
                    'places.notifications.question_answered_body',
                    'place-question-answered:'.$lockedQuestion->stable_key.':'.$recipient->id,
                    ['place' => $lockedPlace->name],
                ));
            }

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
