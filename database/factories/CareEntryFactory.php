<?php

namespace Database\Factories;

use App\Enums\CareEntryStatus;
use App\Enums\CareEntryType;
use App\Enums\CareSourceType;
use App\Models\CareEntry;
use App\Models\CareJournal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CareEntry>
 */
class CareEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = now()->subMinutes(fake()->numberBetween(5, 180));

        return [
            'care_journal_id' => CareJournal::factory(),
            'idempotency_key' => (string) Str::uuid(),
            'type' => CareEntryType::Observation,
            'started_at' => $startedAt,
            'timezone' => 'Europe/Vilnius',
            'status' => CareEntryStatus::Completed,
            'source_type' => CareSourceType::Owner,
            'source_name' => 'Mia Carter',
            'verification_status' => 'person-reported',
            'author_key' => 'mia-carter',
            'author_name' => 'Mia Carter',
            'title' => fake()->sentence(4),
            'notes' => fake()->sentence(),
            'measurements' => [],
            'context' => [],
            'is_unusual' => false,
            'privacy' => 'private',
        ];
    }
}
