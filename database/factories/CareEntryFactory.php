<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CareEntryStatus;
use App\Enums\CareEntryType;
use App\Enums\CareSourceType;
use App\Enums\CareSyncStatus;
use App\Models\CareEntry;
use App\Models\CareJournal;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<CareEntry>
 */
class CareEntryFactory extends ApplicationFactory
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
            'source_recorded_at' => $startedAt,
            'source_timezone' => 'Europe/Vilnius',
            'sync_status' => CareSyncStatus::Direct,
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
