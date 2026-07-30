<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ModerationStatus;
use App\Enums\SearchCaseType;
use App\Enums\SearchStatus;
use App\Models\SearchCase;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<SearchCase>
 */
class SearchCaseFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Scout', 'Nori', 'Luna', 'Kesha']);
        $ownerKey = fake()->unique()->userName();

        return [
            'owner_key' => $ownerKey,
            'owner_name' => fake()->name(),
            'owner_initials' => fake()->lexify('??'),
            'coordinator_key' => $ownerKey,
            'coordinator_name' => fake()->name(),
            'slug' => Str::slug($name.' missing '.Str::random(7)),
            'public_code' => Str::upper(Str::random(8)),
            'active_key' => $ownerKey.':'.Str::lower($name),
            'type' => SearchCaseType::Lost,
            'status' => SearchStatus::Active,
            'moderation_status' => ModerationStatus::Approved,
            'pet_profile_key' => Str::lower($name),
            'pet_name' => $name,
            'species' => 'dog',
            'breed' => 'Mixed breed',
            'sex' => 'male',
            'age_label' => 'Adult',
            'size' => 'medium',
            'primary_color' => 'Black with a white chest',
            'coat' => 'short',
            'distinctive_marks' => 'White chest patch and blue collar.',
            'hidden_marks' => 'Small scar under the left shoulder.',
            'description' => 'Frightened by a loud noise and may keep distance from strangers.',
            'health_notice' => null,
            'approach_instructions' => 'Stay sideways, speak softly, and report the location.',
            'avoid_instructions' => 'Do not chase, surround, or call loudly.',
            'accessories' => ['blue collar'],
            'microchip_status' => 'present',
            'last_seen_area' => 'Vingis Park near the river path',
            'city' => 'Vilnius',
            'country' => 'LT',
            'public_latitude' => 54.683000,
            'public_longitude' => 25.238000,
            'exact_location' => [
                'latitude' => 54.682941,
                'longitude' => 25.237611,
                'note' => 'Near the southern park gate',
            ],
            'direction' => 'East toward the river path',
            'last_seen_at' => now()->subHours(2),
            'reported_at' => now()->subHours(2),
            'notification_radius_km' => 5,
            'visibility' => 'public',
            'alerts_active' => true,
            'volunteer_join_open' => true,
            'animal_secured' => false,
            'contact_protected' => true,
            'contact_details' => ['channel' => 'platform', 'value' => 'mia-carter'],
            'contact_token' => Str::random(48),
            'cover_url' => 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&w=1400&q=85',
            'photos' => [],
            'risk_flags' => [],
            'latest_update' => 'Search teams are checking the river path quietly.',
            'view_count' => fake()->numberBetween(20, 500),
        ];
    }

    public function found(): static
    {
        return $this->state(fn (): array => [
            'active_key' => null,
            'type' => SearchCaseType::Found,
            'status' => SearchStatus::Safe,
            'pet_profile_key' => null,
            'pet_name' => 'Unknown cat',
            'species' => 'cat',
            'animal_secured' => true,
            'alerts_active' => true,
            'last_seen_area' => 'Naujamiestis',
            'description' => 'Found indoors and kept separately from household pets.',
        ]);
    }

    public function returned(): static
    {
        return $this->state(fn (): array => [
            'active_key' => null,
            'status' => SearchStatus::Returned,
            'alerts_active' => false,
            'volunteer_join_open' => false,
            'returned_at' => now(),
            'closed_at' => now(),
            'closure_reason' => 'returned',
        ]);
    }
}
