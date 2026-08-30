<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Place;
use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionAnswer;
use App\Models\PlaceQuestionAnswerVersion;
use App\Models\PlaceQuestionEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class UpdatePlaceQuestionAnswer
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        PlaceQuestionAnswer $answer,
        string $body,
        string $reason,
        string $idempotencyKey,
    ): PlaceQuestionAnswer {
        /** @var array{body: string, reason: string, idempotency_key: string} $validated */
        $validated = validator([
            'body' => trim($body),
            'reason' => trim($reason),
            'idempotency_key' => $idempotencyKey,
        ], [
            'body' => ['required', 'string', 'min:10', 'max:1200'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();

        $existing = PlaceQuestionAnswerVersion::query()
            ->where('editor_user_id', $actor->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();
        if ($existing !== null) {
            if ($existing->place_question_answer_id !== $answer->id
                || $existing->body !== $validated['body']
                || $existing->reason !== $validated['reason']) {
                throw ValidationException::withMessages([
                    'place_idempotency_key' => __('places.validation.answer_idempotency_conflict'),
                ]);
            }

            return PlaceQuestionAnswer::query()->findOrFail($answer->id);
        }

        return DB::transaction(function () use ($actor, $answer, $validated): PlaceQuestionAnswer {
            $questionId = PlaceQuestionAnswer::query()->whereKey($answer->id)->value('place_question_id');
            $question = PlaceQuestion::query()->findOrFail($questionId);
            $place = Place::query()->with('organization')->lockForUpdate()->findOrFail($question->place_id);
            $lockedQuestion = PlaceQuestion::query()->lockForUpdate()->findOrFail($question->id);
            $lockedAnswer = PlaceQuestionAnswer::query()->lockForUpdate()->findOrFail($answer->id);
            $lockedQuestion->setRelation('place', $place);
            $lockedAnswer->setRelation('question', $lockedQuestion);
            $this->gate->forUser($actor)->authorize('updateAnswer', [$lockedQuestion, $lockedAnswer]);

            $nextVersion = $lockedAnswer->current_version + 1;
            PlaceQuestionAnswerVersion::query()->create([
                'place_question_answer_id' => $lockedAnswer->id,
                'editor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'version' => $nextVersion,
                'body' => $validated['body'],
                'reason' => $validated['reason'],
                'created_at' => now(),
            ]);
            $lockedAnswer->forceFill([
                'body' => $validated['body'],
                'correction_reason' => $validated['reason'],
                'current_version' => $nextVersion,
            ])->save();
            PlaceQuestionEvent::query()->create([
                'place_question_id' => $lockedQuestion->id,
                'actor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'event_type' => 'answer_updated',
                'public_summary_key' => 'places.questions.events.answer_updated',
                'created_at' => now(),
            ]);

            return $lockedAnswer;
        }, 3);
    }
}
