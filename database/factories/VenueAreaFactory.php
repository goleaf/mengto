<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceAccessibilityStatus;
use App\Enums\VenueAreaType;
use App\Models\Venue;
use App\Models\VenueArea;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<VenueArea> */
final class VenueAreaFactory extends ApplicationFactory
{
    protected $model = VenueArea::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'venue_id' => Venue::factory(),
            'stable_key' => Str::slug($name).'-'.Str::lower((string) Str::ulid()),
            'name' => Str::headline($name),
            'type' => VenueAreaType::MainHall,
            'is_public' => true,
            'human_capacity' => 30,
            'animal_capacity' => 10,
            'species_capacities' => ['dog' => 10],
            'accessibility_status' => PlaceAccessibilityStatus::NotAssessed,
            'accessibility_facts' => [],
            'private_instructions' => null,
        ];
    }

    public function quietArea(): static
    {
        return $this->state(fn (): array => [
            'type' => VenueAreaType::QuietArea,
            'name' => 'Quiet animal rest area',
            'is_public' => false,
        ]);
    }
}
