<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceReviewAnonymityMode;
use App\Models\PlaceReview;
use App\Models\PlaceReviewEvent;
use App\Models\PlaceReviewVersion;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class UpdatePlaceReview
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        PlaceReview $review,
        int $ratingOverall,
        ?int $ratingService,
        ?int $ratingAccessibility,
        ?int $ratingPetFriendliness,
        string $body,
        PlaceReviewAnonymityMode $anonymityMode,
        string $reason,
        string $idempotencyKey,
    ): PlaceReview {
        /** @var array{rating_overall: int, rating_service: int|null, rating_accessibility: int|null, rating_pet_friendliness: int|null, body: string, anonymity_mode: string, reason: string, idempotency_key: string} $validated */
        $validated = validator([
            'rating_overall' => $ratingOverall,
            'rating_service' => $ratingService,
            'rating_accessibility' => $ratingAccessibility,
            'rating_pet_friendliness' => $ratingPetFriendliness,
            'body' => trim($body),
            'anonymity_mode' => $anonymityMode->value,
            'reason' => trim($reason),
            'idempotency_key' => $idempotencyKey,
        ], [
            'rating_overall' => ['required', 'integer', 'between:1,5'],
            'rating_service' => ['nullable', 'integer', 'between:1,5'],
            'rating_accessibility' => ['nullable', 'integer', 'between:1,5'],
            'rating_pet_friendliness' => ['nullable', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:10', 'max:4000'],
            'anonymity_mode' => ['required', 'in:named,anonymous'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();

        $this->gate->forUser($actor)->authorize('update', $review);

        $replay = PlaceReviewVersion::query()
            ->where('editor_user_id', $actor->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();
        if ($replay instanceof PlaceReviewVersion) {
            return $this->validatedReplay($replay, $review, $validated);
        }

        return DB::transaction(function () use ($actor, $review, $validated): PlaceReview {
            $locked = PlaceReview::query()->withTrashed()->lockForUpdate()->findOrFail($review->id);
            $this->gate->forUser($actor)->authorize('update', $locked);
            $nextVersion = $locked->current_version + 1;

            $locked->forceFill([
                'rating_overall' => $validated['rating_overall'],
                'rating_service' => $validated['rating_service'],
                'rating_accessibility' => $validated['rating_accessibility'],
                'rating_pet_friendliness' => $validated['rating_pet_friendliness'],
                'body' => $validated['body'],
                'anonymity_mode' => $validated['anonymity_mode'],
                'current_version' => $nextVersion,
            ])->save();
            PlaceReviewVersion::query()->create([
                'place_review_id' => $locked->id,
                'editor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'version' => $nextVersion,
                'rating_overall' => $locked->rating_overall,
                'rating_service' => $locked->rating_service,
                'rating_accessibility' => $locked->rating_accessibility,
                'rating_pet_friendliness' => $locked->rating_pet_friendliness,
                'body' => $locked->body,
                'anonymity_mode' => $locked->anonymity_mode->value,
                'reason' => $validated['reason'],
            ]);
            PlaceReviewEvent::query()->create([
                'place_review_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'event_type' => 'updated',
                'from_status' => $locked->moderation_status->value,
                'to_status' => $locked->moderation_status->value,
                'public_summary_key' => 'places.reviews.events.updated',
            ]);

            return $locked->load(['versions', 'events']);
        }, 3);
    }

    /** @param array{rating_overall: int, rating_service: int|null, rating_accessibility: int|null, rating_pet_friendliness: int|null, body: string, anonymity_mode: string, reason: string, idempotency_key: string} $validated */
    private function validatedReplay(PlaceReviewVersion $version, PlaceReview $review, array $validated): PlaceReview
    {
        if ($version->place_review_id !== $review->id || $version->rating_overall !== $validated['rating_overall']
            || $version->rating_service !== $validated['rating_service']
            || $version->rating_accessibility !== $validated['rating_accessibility']
            || $version->rating_pet_friendliness !== $validated['rating_pet_friendliness']
            || $version->body !== $validated['body'] || $version->anonymity_mode !== $validated['anonymity_mode']
            || $version->reason !== $validated['reason']) {
            throw ValidationException::withMessages(['place_idempotency_key' => __('validation.prohibited')]);
        }

        return $review->fresh(['versions', 'events']) ?? $review;
    }
}
