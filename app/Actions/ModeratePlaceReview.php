<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceReviewModerationStatus;
use App\Models\PlaceReview;
use App\Models\PlaceReviewEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ModeratePlaceReview
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        PlaceReview $review,
        PlaceReviewModerationStatus $status,
        string $reason,
        string $idempotencyKey,
    ): PlaceReview {
        if ($status === PlaceReviewModerationStatus::Pending) {
            throw ValidationException::withMessages(['moderation_status' => __('validation.prohibited')]);
        }

        /** @var array{reason: string, idempotency_key: string} $validated */
        $validated = validator([
            'reason' => trim($reason),
            'idempotency_key' => $idempotencyKey,
        ], [
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();
        $this->gate->forUser($actor)->authorize('moderate', $review);

        $replay = PlaceReviewEvent::query()
            ->where('actor_user_id', $actor->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();
        if ($replay instanceof PlaceReviewEvent) {
            if ($replay->place_review_id !== $review->id || $replay->event_type !== 'moderated'
                || $replay->to_status !== $status->value || $replay->private_note !== $validated['reason']) {
                throw ValidationException::withMessages(['place_idempotency_key' => __('validation.prohibited')]);
            }

            return $review->fresh(['events']) ?? $review;
        }

        return DB::transaction(function () use ($actor, $review, $status, $validated): PlaceReview {
            $locked = PlaceReview::query()->withTrashed()->lockForUpdate()->findOrFail($review->id);
            $this->gate->forUser($actor)->authorize('moderate', $locked);
            $from = $locked->moderation_status;
            $locked->forceFill([
                'moderator_user_id' => $actor->id,
                'moderation_status' => $status,
                'moderation_reason' => $validated['reason'],
            ])->save();
            PlaceReviewEvent::query()->create([
                'place_review_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'event_type' => 'moderated',
                'from_status' => $from->value,
                'to_status' => $status->value,
                'public_summary_key' => 'places.reviews.events.moderated',
                'private_note' => $validated['reason'],
            ]);

            return $locked->load('events');
        }, 3);
    }
}
