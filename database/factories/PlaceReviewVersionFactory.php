<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceReviewAnonymityMode;
use App\Models\PlaceReview;
use App\Models\PlaceReviewVersion;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceReviewVersion> */
final class PlaceReviewVersionFactory extends ApplicationFactory
{
    protected $model = PlaceReviewVersion::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_review_id' => PlaceReview::factory(),
            'editor_user_id' => User::factory(),
            'idempotency_key' => (string) Str::uuid(),
            'version' => 1,
            'rating_overall' => fake()->numberBetween(1, 5),
            'rating_service' => null,
            'rating_accessibility' => null,
            'rating_pet_friendliness' => null,
            'body' => fake()->paragraph(),
            'anonymity_mode' => PlaceReviewAnonymityMode::Named->value,
            'reason' => null,
            'created_at' => now(),
        ];
    }
}
