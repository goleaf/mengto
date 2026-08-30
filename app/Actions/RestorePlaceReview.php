<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PlaceReview;
use App\Models\PlaceReviewEvent;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RestorePlaceReview
{
    public function __construct(private Gate $gate) {}

    public function handle(User $actor, PlaceReview $review, string $idempotencyKey): PlaceReview
    {
        validator(['idempotency_key' => $idempotencyKey], [
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();
        $this->gate->forUser($actor)->authorize('restore', $review);

        $replay = PlaceReviewEvent::query()
            ->where('actor_user_id', $actor->id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
        if ($replay instanceof PlaceReviewEvent) {
            if ($replay->place_review_id !== $review->id || $replay->event_type !== 'restored') {
                throw ValidationException::withMessages(['place_idempotency_key' => __('validation.prohibited')]);
            }

            return $review->fresh(['events']) ?? $review;
        }

        return DB::transaction(function () use ($actor, $review, $idempotencyKey): PlaceReview {
            $locked = PlaceReview::query()->withTrashed()->lockForUpdate()->findOrFail($review->id);
            $this->gate->forUser($actor)->authorize('restore', $locked);
            $locked->restore();
            $locked->forceFill(['restored_at' => now()])->save();
            PlaceReviewEvent::query()->create([
                'place_review_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'idempotency_key' => $idempotencyKey,
                'event_type' => 'restored',
                'from_status' => $locked->moderation_status->value,
                'to_status' => $locked->moderation_status->value,
                'public_summary_key' => 'places.reviews.events.restored',
            ]);

            return $locked->load('events');
        }, 3);
    }
}
