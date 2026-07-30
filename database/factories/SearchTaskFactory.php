<?php

namespace Database\Factories;

use App\Enums\SearchTaskStatus;
use App\Models\SearchCase;
use App\Models\SearchTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchTask>
 */
class SearchTaskFactory extends Factory
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
            'attachments' => [],
            'version' => 1,
        ];
    }
}
