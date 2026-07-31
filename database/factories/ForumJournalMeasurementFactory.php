<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumJournalEntry;
use App\Models\ForumJournalMeasurement;

/**
 * @extends ApplicationFactory<ForumJournalMeasurement>
 */
final class ForumJournalMeasurementFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_journal_entry_id' => ForumJournalEntry::factory(),
            'metric_key' => 'weight_kg',
            'numeric_value' => fake()->randomFloat(2, 0.1, 100),
            'unit' => 'kg',
            'position' => 1,
        ];
    }
}
