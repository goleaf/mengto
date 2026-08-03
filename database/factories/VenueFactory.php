<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VenueStatus;
use App\Models\Organization;
use App\Models\Place;
use App\Models\Venue;

/** @extends ApplicationFactory<Venue> */
final class VenueFactory extends ApplicationFactory
{
    protected $model = Venue::class;

    public function definition(): array
    {
        return [
            'place_id' => Place::factory(),
            'organization_id' => null,
            'status' => VenueStatus::Active,
            'timezone' => 'Europe/Vilnius',
            'human_capacity' => 60,
            'animal_capacity' => 20,
            'species_capacities' => ['dog' => 20],
            'staff_to_participant_ratio' => 10,
            'operational_contact' => fake()->phoneNumber(),
            'operational_rules' => ['Keep emergency exits clear.'],
            'confirmed_at' => now(),
            'information_expires_at' => now()->addYear(),
        ];
    }

    public function forOrganization(?Organization $organization = null): static
    {
        $organization ??= Organization::factory()->create();

        return $this
            ->for($organization)
            ->for(Place::factory()->forOrganization($organization), 'place');
    }
}
