<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceQuestionStatus;
use App\Models\Place;
use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ClosePlaceQuestion
{
    public function __construct(private Gate $gate) {}

    public function handle(User $actor, PlaceQuestion $question, string $reason, string $idempotencyKey): PlaceQuestion
    {
        return $this->transition($actor, $question, $reason, $idempotencyKey);
    }

    private function transition(User $actor, PlaceQuestion $question, string $reason, string $idempotencyKey): PlaceQuestion
    {
        /** @var array{reason: string, idempotency_key: string} $validated */
        $validated = validator(['reason' => trim($reason), 'idempotency_key' => $idempotencyKey], [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();
        $existing = PlaceQuestionEvent::query()
            ->where('actor_user_id', $actor->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();
        if ($existing !== null) {
            if ($existing->place_question_id !== $question->id || $existing->event_type !== 'closed') {
                throw ValidationException::withMessages(['place_idempotency_key' => __('places.validation.question_idempotency_conflict')]);
            }

            return PlaceQuestion::query()->findOrFail($question->id);
        }

        return DB::transaction(function () use ($actor, $question, $validated): PlaceQuestion {
            $placeId = PlaceQuestion::query()->whereKey($question->id)->value('place_id');
            $place = Place::query()->with('organization')->lockForUpdate()->findOrFail($placeId);
            $locked = PlaceQuestion::query()->lockForUpdate()->findOrFail($question->id);
            $locked->setRelation('place', $place);
            $this->gate->forUser($actor)->authorize('close', $locked);
            if (in_array($locked->status, [PlaceQuestionStatus::Hidden, PlaceQuestionStatus::Removed], true)) {
                throw ValidationException::withMessages(['place_question' => __('places.validation.question_unavailable')]);
            }
            $from = $locked->status;
            $locked->forceFill([
                'status' => PlaceQuestionStatus::Closed,
                'closed_by_user_id' => $actor->id,
                'closed_at' => now(),
                'close_reason' => $validated['reason'],
            ])->save();
            PlaceQuestionEvent::query()->create([
                'place_question_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'event_type' => 'closed',
                'from_status' => $from->value,
                'to_status' => PlaceQuestionStatus::Closed->value,
                'public_summary_key' => 'places.questions.events.closed',
                'private_note' => $validated['reason'],
                'created_at' => now(),
            ]);

            return $locked;
        }, 3);
    }
}
