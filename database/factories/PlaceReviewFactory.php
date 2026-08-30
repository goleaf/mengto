<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceReviewAnonymityMode;
use App\Enums\PlaceReviewEligibilityContext;
use App\Enums\PlaceReviewModerationStatus;
use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceReview> */
final class PlaceReviewFactory extends ApplicationFactory
{
    protected $model = PlaceReview::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_id' => Place::factory(),
            'author_user_id' => User::factory(),
            'pet_profile_id' => null,
            'moderator_user_id' => null,
            'stable_key' => 'place-review-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'eligibility_context' => PlaceReviewEligibilityContext::Other,
            'verified_visit' => false,
            'rating_overall' => fake()->numberBetween(1, 5),
            'rating_service' => null,
            'rating_accessibility' => null,
            'rating_pet_friendliness' => null,
            'body' => fake()->paragraph(),
            'anonymity_mode' => PlaceReviewAnonymityMode::Named,
            'moderation_status' => PlaceReviewModerationStatus::Published,
            'current_version' => 1,
            'moderation_reason' => null,
            'deletion_reason' => null,
            'restored_at' => null,
        ];
    }
}
