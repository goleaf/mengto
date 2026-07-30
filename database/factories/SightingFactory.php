<?php

namespace Database\Factories;

use App\Enums\SightingStatus;
use App\Models\SearchCase;
use App\Models\Sighting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Sighting>
 */
class SightingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'search_case_id' => SearchCase::factory(),
            'reporter_key' => fake()->userName(),
            'reporter_name' => fake()->name(),
            'idempotency_key' => (string) Str::uuid(),
            'status' => SightingStatus::Submitted,
            'observed_at' => now()->subMinutes(25),
            'submitted_at' => now(),
            'time_accuracy' => 'within-30-minutes',
            'public_area' => 'Vingis Park east path',
            'public_latitude' => 54.684000,
            'public_longitude' => 25.244000,
            'exact_location' => [
                'latitude' => 54.683812,
                'longitude' => 25.243774,
            ],
            'direction' => 'East',
            'distance' => 'About 30 metres',
            'confidence' => 'very-similar',
            'contact_status' => 'seen-only',
            'animal_condition' => 'Moving normally but frightened',
            'danger' => null,
            'notes' => 'Kept distance and did not pursue.',
            'is_anonymous' => false,
            'exact_location_public' => false,
            'risk_flags' => [],
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (): array => [
            'status' => SightingStatus::Confirmed,
            'verified_by_key' => 'mia-carter',
            'verified_at' => now(),
        ]);
    }
}
