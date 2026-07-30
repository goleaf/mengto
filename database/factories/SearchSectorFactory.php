<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SearchSectorStatus;
use App\Models\SearchCase;
use App\Models\SearchSector;

/**
 * @extends ApplicationFactory<SearchSector>
 */
class SearchSectorFactory extends ApplicationFactory
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
