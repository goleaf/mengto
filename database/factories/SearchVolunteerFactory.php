<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SearchVolunteerStatus;
use App\Models\SearchCase;
use App\Models\SearchVolunteer;

/**
 * @extends ApplicationFactory<SearchVolunteer>
 */
class SearchVolunteerFactory extends ApplicationFactory
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
