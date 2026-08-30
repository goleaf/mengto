<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceReviewAnonymityMode;
use App\Enums\PlaceReviewEligibilityContext;
use App\Enums\PlaceReviewModerationStatus;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\PlaceReviewEvent;
use App\Models\PlaceReviewVersion;
use App\Models\User;
use App\Models\UserDomainState;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class SubmitPlaceReview
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        Place $place,
        int $ratingOverall,
        ?int $ratingService,
        ?int $ratingAccessibility,
        ?int $ratingPetFriendliness,
        string $body,
        PlaceReviewAnonymityMode $anonymityMode,
        ?int $petProfileId,
        string $idempotencyKey,
    ): PlaceReview {
        /** @var array{rating_overall: int, rating_service: int|null, rating_accessibility: int|null, rating_pet_friendliness: int|null, body: string, anonymity_mode: string, pet_profile_id: int|null, idempotency_key: string} $validated */
        $validated = validator([
            'rating_overall' => $ratingOverall,
            'rating_service' => $ratingService,
            'rating_accessibility' => $ratingAccessibility,
            'rating_pet_friendliness' => $ratingPetFriendliness,
            'body' => trim($body),
            'anonymity_mode' => $anonymityMode->value,
            'pet_profile_id' => $petProfileId,
            'idempotency_key' => $idempotencyKey,
        ], [
            'rating_overall' => ['required', 'integer', 'between:1,5'],
            'rating_service' => ['nullable', 'integer', 'between:1,5'],
            'rating_accessibility' => ['nullable', 'integer', 'between:1,5'],
            'rating_pet_friendliness' => ['nullable', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:10', 'max:4000'],
            'anonymity_mode' => ['required', 'in:named,anonymous'],
            'pet_profile_id' => ['nullable', 'integer', 'exists:pet_profiles,id'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();

        $this->gate->forUser($actor)->authorize('create', [PlaceReview::class, $place]);

        $existing = PlaceReview::query()->withTrashed()
            ->where('author_user_id', $actor->id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();

        if ($existing instanceof PlaceReview) {
            return $this->validatedReplay($existing, $actor, $place, $validated);
        }

        return DB::transaction(function () use ($actor, $place, $validated): PlaceReview {
            $lockedPlace = Place::query()
                ->with('organization')
                ->lockForUpdate()
                ->findOrFail($place->id);
            $this->gate->forUser($actor)->authorize('create', [PlaceReview::class, $lockedPlace]);

            $existingForPlace = PlaceReview::query()->withTrashed()
                ->where('place_id', $lockedPlace->id)
                ->where('author_user_id', $actor->id)
                ->lockForUpdate()
                ->first();

            if ($existingForPlace instanceof PlaceReview) {
                throw ValidationException::withMessages([
                    'place_review' => __('validation.prohibited'),
                ]);
            }

            $this->assertPetProfileIsManagedBy($actor, $validated['pet_profile_id']);
            $verifiedVisit = $this->hasCompatibilityVisit($actor, $lockedPlace);
            $review = PlaceReview::query()->create([
                'place_id' => $lockedPlace->id,
                'author_user_id' => $actor->id,
                'pet_profile_id' => $validated['pet_profile_id'],
                'stable_key' => 'place-review-'.Str::lower((string) Str::ulid()),
                'idempotency_key' => $validated['idempotency_key'],
                'eligibility_context' => $verifiedVisit
                    ? PlaceReviewEligibilityContext::Visit
                    : PlaceReviewEligibilityContext::Other,
                'verified_visit' => $verifiedVisit,
                'rating_overall' => $validated['rating_overall'],
                'rating_service' => $validated['rating_service'],
                'rating_accessibility' => $validated['rating_accessibility'],
                'rating_pet_friendliness' => $validated['rating_pet_friendliness'],
                'body' => $validated['body'],
                'anonymity_mode' => $validated['anonymity_mode'],
                'moderation_status' => PlaceReviewModerationStatus::Published,
                'current_version' => 1,
            ]);

            PlaceReviewVersion::query()->create([
                'place_review_id' => $review->id,
                'editor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'version' => 1,
                'rating_overall' => $review->rating_overall,
                'rating_service' => $review->rating_service,
                'rating_accessibility' => $review->rating_accessibility,
                'rating_pet_friendliness' => $review->rating_pet_friendliness,
                'body' => $review->body,
                'anonymity_mode' => $review->anonymity_mode->value,
            ]);
            PlaceReviewEvent::query()->create([
                'place_review_id' => $review->id,
                'actor_user_id' => $actor->id,
                'idempotency_key' => $validated['idempotency_key'],
                'event_type' => 'submitted',
                'to_status' => $review->moderation_status->value,
                'public_summary_key' => 'places.reviews.events.submitted',
            ]);

            return $review->load(['author', 'versions', 'events']);
        }, 3);
    }

    /** @param array{rating_overall: int, rating_service: int|null, rating_accessibility: int|null, rating_pet_friendliness: int|null, body: string, anonymity_mode: string, pet_profile_id: int|null, idempotency_key: string} $validated */
    private function validatedReplay(PlaceReview $review, User $actor, Place $place, array $validated): PlaceReview
    {
        if ($review->author_user_id !== $actor->id || $review->place_id !== $place->id
            || $review->rating_overall !== $validated['rating_overall']
            || $review->rating_service !== $validated['rating_service']
            || $review->rating_accessibility !== $validated['rating_accessibility']
            || $review->rating_pet_friendliness !== $validated['rating_pet_friendliness']
            || $review->body !== $validated['body']
            || $review->anonymity_mode->value !== $validated['anonymity_mode']
            || $review->pet_profile_id !== $validated['pet_profile_id']) {
            throw ValidationException::withMessages([
                'place_idempotency_key' => __('validation.prohibited'),
            ]);
        }

        return $review->loadMissing(['author', 'versions', 'events']);
    }

    private function assertPetProfileIsManagedBy(User $actor, ?int $petProfileId): void
    {
        if ($petProfileId === null) {
            return;
        }

        $profile = PetProfile::query()->select(['id', 'user_id'])->find($petProfileId);
        $managed = $profile?->user_id === $actor->id
            || PetProfileManager::query()
                ->activeAt(now())
                ->where('pet_profile_id', $petProfileId)
                ->where('user_id', $actor->id)
                ->lockForUpdate()
                ->exists();

        if (! $managed) {
            throw ValidationException::withMessages(['pet_profile_id' => __('validation.prohibited')]);
        }
    }

    private function hasCompatibilityVisit(User $actor, Place $place): bool
    {
        $payload = UserDomainState::query()
            ->where('user_id', $actor->id)
            ->where('namespace', 'places.state.v1')
            ->value('payload');

        return is_array($payload) && isset($payload['visited'][$place->stable_key]);
    }
}
