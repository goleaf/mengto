<?php

namespace Database\Factories;

use App\Models\SearchAlert;
use App\Models\SearchCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchAlert>
 */
class SearchAlertFactory extends Factory
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
