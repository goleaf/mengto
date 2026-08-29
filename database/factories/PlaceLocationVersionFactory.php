<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Place;
use App\Models\PlaceLocationVersion;

/** @extends ApplicationFactory<PlaceLocationVersion> */
final class PlaceLocationVersionFactory extends ApplicationFactory
{
    protected $model = PlaceLocationVersion::class;

    public function definition(): array
    {
        return [
            'place_id' => Place::factory(),
            'changed_by_user_id' => null,
            'version' => fn (array $attributes): int => (int) PlaceLocationVersion::query()
                ->where('place_id', $attributes['place_id'])
                ->max('version') + 1,
            'public_region' => 'Vilnius',
            'public_address' => null,
            'public_latitude' => '54.687000',
            'public_longitude' => '25.279000',
            'exact_address' => fake()->streetAddress(),
            'exact_latitude' => '54.687234',
            'exact_longitude' => '25.279734',
            'private_instructions' => fake()->sentence(),
            'reason_code' => 'factory-location-version',
            'created_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (PlaceLocationVersion $version): void {
            $version->changed_by_user_id ??= Place::query()
                ->findOrFail($version->place_id)
                ->owner_user_id;
        });
    }
}
