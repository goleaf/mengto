<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ListingReport;

/**
 * @extends ApplicationFactory<ListingReport>
 */
class ListingReportFactory extends ApplicationFactory
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
