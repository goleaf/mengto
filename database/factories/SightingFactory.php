<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SightingStatus;
use App\Models\SearchCase;
use App\Models\Sighting;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<Sighting>
 */
class SightingFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'search_case_id' => SearchCase::factory(),
            'reporter_id' => null,
            'reporter_key' => fake()->userName(),
            'reporter_name' => fake()->name(),
            'idempotency_key' => (string) Str::uuid(),
            'status' => SightingStatus::Submitted,
            'observed_at' => now()->subMinutes(25),
            'submitted_at' => now(),
            'time_accuracy' => 'within-30-minutes',
            'public_area' => 'Vingis Park east path',
            'public_latitude' => '54.684000',
            'public_longitude' => '25.244000',
            'exact_location' => [
                'latitude' => '54.683812',
                'longitude' => '25.243774',
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
            'risk_flags' => ['location-sensitive'],
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

    public function configure(): static
    {
        return $this->afterMaking(static function (Sighting $sighting): void {
            if ($sighting->reporter_id === null) {
                return;
            }

            $reporter = User::query()->findOrFail($sighting->reporter_id);
            $sighting->reporter_key = $reporter->actor_key;
            $sighting->reporter_name = $reporter->name;
        });
    }
}
