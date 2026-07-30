<?php

namespace Database\Factories;

use App\Enums\CareEntryType;
use App\Enums\CareTaskPriority;
use App\Enums\CareTaskStatus;
use App\Models\CareJournal;
use App\Models\CareTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareTask>
 */
class CareTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'care_journal_id' => CareJournal::factory(),
            'title' => fake()->randomElement(['Refresh water', 'Evening walk', 'Brush coat']),
            'type' => CareEntryType::Observation,
            'assignee_key' => 'mia-carter',
            'assignee_name' => 'Mia Carter',
            'due_at' => now()->addHour(),
            'timezone' => 'Europe/Vilnius',
            'priority' => CareTaskPriority::Normal,
            'status' => CareTaskStatus::Planned,
            'instructions' => fake()->sentence(),
            'requires_individual_confirmation' => false,
            'created_by_key' => 'mia-carter',
            'created_by_name' => 'Mia Carter',
        ];
    }
}
