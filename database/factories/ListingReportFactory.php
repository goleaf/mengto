<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ListingReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingReport>
 */
class ListingReportFactory extends Factory
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
            'reporter_key' => fake()->unique()->userName(),
            'reason' => 'misleading',
            'details' => fake()->sentence(),
            'priority' => 'normal',
            'status' => 'open',
        ];
    }
}
