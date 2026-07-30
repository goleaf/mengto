<?php

namespace Database\Factories;

use App\Enums\CareRoutineStatus;
use App\Models\CareJournal;
use App\Models\CareRoutine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareRoutine>
 */
class CareRoutineFactory extends Factory
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
            'name' => fake()->randomElement(['Morning care', 'Evening wind-down', 'Weekend routine']),
            'period' => 'daily',
            'starts_on' => today(),
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'start_time' => '08:00',
            'timezone' => 'Europe/Vilnius',
            'status' => CareRoutineStatus::Active,
            'version' => 1,
            'instructions' => fake()->sentence(),
            'created_by_key' => 'mia-carter',
            'created_by_name' => 'Mia Carter',
        ];
    }
}
