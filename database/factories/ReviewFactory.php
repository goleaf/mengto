<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\ExpertProfile;
use App\Models\Review;

/** @extends ApplicationFactory<Review> */
class ReviewFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'expert_profile_id' => ExpertProfile::factory(),
            'reviewer_key' => fake()->unique()->userName(),
            'reviewer_name' => fake()->name(),
            'is_verified_client' => true,
            'is_anonymous' => false,
            'rating' => 5,
            'communication_rating' => 5,
            'clarity_rating' => 5,
            'organization_rating' => 5,
            'price_transparency_rating' => 5,
            'body' => fake()->paragraphs(2, true),
            'status' => ReviewStatus::Published,
        ];
    }
}
