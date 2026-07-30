<?php

namespace Database\Factories;

use App\Models\SearchCase;
use App\Models\SearchReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchReport>
 */
class SearchReportFactory extends Factory
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
