<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SearchCase;
use App\Models\SearchReport;

/**
 * @extends ApplicationFactory<SearchReport>
 */
class SearchReportFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'search_case_id' => SearchCase::factory(),
            'reporter_key' => fake()->userName(),
            'reason' => 'outdated',
            'details' => fake()->sentence(),
            'priority' => 'normal',
            'status' => 'open',
        ];
    }
}
