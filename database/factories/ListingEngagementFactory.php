<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ListingEngagement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingEngagement>
 */
class ListingEngagementFactory extends Factory
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
