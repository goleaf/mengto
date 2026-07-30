<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SearchAlert;
use App\Models\SearchCase;

/**
 * @extends ApplicationFactory<SearchAlert>
 */
class SearchAlertFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'search_case_id' => SearchCase::factory(),
            'kind' => 'local-urgent',
            'radius_km' => 5,
            'region' => 'Vilnius',
            'channels' => ['in-app', 'push'],
            'audiences' => ['nearby-users', 'shelters', 'clinics'],
            'status' => 'sent',
            'recipient_count' => fake()->numberBetween(120, 2500),
            'message' => 'Local missing pet alert.',
            'sent_at' => now(),
        ];
    }
}
