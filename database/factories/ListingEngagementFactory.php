<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ListingEngagement;

/**
 * @extends ApplicationFactory<ListingEngagement>
 */
class ListingEngagementFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'user_key' => fake()->unique()->userName(),
            'is_saved' => true,
            'last_viewed_at' => now(),
        ];
    }
}
