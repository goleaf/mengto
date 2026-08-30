<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SearchTaskStatus;
use App\Models\SearchCase;
use App\Models\SearchTask;

/**
 * @extends ApplicationFactory<SearchTask>
 */
class SearchTaskFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'search_case_id' => SearchCase::factory(),
            'created_by_key' => 'mia-carter',
            'type' => 'search-area',
            'title' => fake()->sentence(4),
            'description' => 'Check the assigned public area quietly and report observations.',
            'status' => SearchTaskStatus::Open,
            'safety_level' => 'standard',
            'starts_at' => now()->addHour(),
            'due_at' => now()->addHours(3),
            'attachments' => [[
                'name' => 'sector-map.jpg',
                'url' => asset('images/places/park-primary-lg.jpg'),
            ]],
            'version' => 1,
        ];
    }
}
