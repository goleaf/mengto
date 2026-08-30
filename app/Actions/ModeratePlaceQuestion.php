<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionEvent;
use App\Models\User;
use App\Services\PlaceContributionNotifier;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class ModeratePlaceQuestion
{
    public function __construct(private Gate $gate, private PlaceContributionNotifier $notifier) {}

    public function handle(User $actor, PlaceQuestion $question, string $status, string $reason, string $idempotencyKey): PlaceQuestion
    {
        /** @var array{status: string, reason: string, idempotency_key: string} $validated */
        $validated = validator([
            'status' => $status,
            'reason' => trim($reason),
            'idempotency_key' => $idempotencyKey,
        ], [
            'status' => ['required', Rule::in(['approved', 'hidden', 'removed'])],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();
        $existing = PlaceQuestionEvent::query()
            ->where('actor_user_id', $actor->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();
        if ($existing !== null) {
            if ($existing->place_question_id !== $question->id
                || $existing->event_type !== 'moderated'
                || $existing->to_status !== $validated['status']) {
                throw ValidationException::withMessages(['place_idempotency_key' => __('places.validation.question_idempotency_conflict')]);
            }

            return PlaceQuestion::query()->findOrFail($question->id);
        }

        return DB::transaction(function () use ($actor, $question, $validated): PlaceQuestion {
            $locked = PlaceQuestion::query()->with(['place', 'author'])->lockForUpdate()->findOrFail($question->id);
            $this->gate->forUser($actor)->authorize('moderate', $locked);
            $from = $locked->moderation_status;
            $locked->forceFill(['moderation_status' => $validated['status']])->save();
            PlaceQuestionEvent::query()->create([
                'place_question_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'event_type' => 'moderated',
                'from_status' => $from,
                'to_status' => $validated['status'],
                'public_summary_key' => 'places.questions.events.moderated',
                'private_note' => $validated['reason'],
                'created_at' => now(),
            ]);
            $recipient = $locked->author;
            DB::afterCommit(fn () => $this->notifier->send(
                $recipient,
                'place_question_moderated',
                'places.notifications.question_moderated_title',
                'places.notifications.question_moderated_body',
                'place-question-moderated:'.$locked->stable_key.':'.$validated['status'].':'.$recipient->id,
                ['place' => $locked->place->name],
            ));

            return $locked;
        }, 3);
    }
}
