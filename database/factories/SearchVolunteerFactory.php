<?php

namespace Database\Factories;

use App\Enums\SearchVolunteerStatus;
use App\Models\SearchCase;
use App\Models\SearchVolunteer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchVolunteer>
 */
class SearchVolunteerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'search_case_id' => SearchCase::factory(),
            'actor_key' => fake()->unique()->userName(),
            'display_name' => fake()->name(),
            'role' => 'volunteer',
            'capabilities' => ['walking-search', 'posters'],
            'status' => SearchVolunteerStatus::Active,
            'privacy_level' => 'team-only',
            'available_until' => now()->addHours(4),
            'joined_at' => now(),
        ];
    }
}
