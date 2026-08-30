<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SearchCase;
use App\Models\SearchReport;
use App\Models\User;

/**
 * @extends ApplicationFactory<SearchReport>
 */
class SearchReportFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'search_case_id' => SearchCase::factory(),
            'reporter_id' => null,
            'reporter_key' => fake()->userName(),
            'reason' => 'outdated',
            'details' => fake()->sentence(),
            'priority' => 'normal',
            'status' => 'open',
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(static function (SearchReport $report): void {
            if ($report->reporter_id !== null) {
                $report->reporter_key = User::query()
                    ->whereKey($report->reporter_id)
                    ->valueOrFail('actor_key');
            }
        });
    }
}
