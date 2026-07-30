<?php

namespace Database\Factories;

use App\Enums\SearchSectorStatus;
use App\Models\SearchCase;
use App\Models\SearchSector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchSector>
 */
class SearchSectorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'search_case_id' => SearchCase::factory(),
            'code' => fake()->unique()->bothify('S-##'),
            'label' => fake()->streetName(),
            'status' => SearchSectorStatus::Unchecked,
            'priority' => 2,
            'map_bounds' => ['x' => 20, 'y' => 20, 'width' => 24, 'height' => 22],
            'risk_notes' => null,
            'access_notes' => 'Use public paths only.',
        ];
    }
}
