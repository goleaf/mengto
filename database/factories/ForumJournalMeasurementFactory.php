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
            'metric_key' => function (array $attributes): string {
                $existing = ForumJournalMeasurement::query()
                    ->where('forum_journal_entry_id', $attributes['forum_journal_entry_id'])
                    ->pluck('metric_key');

                return collect([
                    'weight_kg',
                    'duration_minutes',
                    'intensity_score',
                    'distance_km',
                    'temperature_c',
                    'pulse_bpm',
                    'respiratory_rate',
                    'food_grams',
                    'water_ml',
                    'sleep_hours',
                ])->first(static fn (string $key): bool => ! $existing->contains($key))
                    ?? 'observation_count';
            },
            'numeric_value' => fake()->randomElement(['4.2500', '12.8000', '24.1500']),
            'unit' => fn (array $attributes): string => match ($attributes['metric_key']) {
                'weight_kg' => 'kg',
                'duration_minutes' => 'minutes',
                'distance_km' => 'km',
                'temperature_c' => 'celsius',
                'pulse_bpm' => 'bpm',
                'respiratory_rate' => 'breaths_per_minute',
                'food_grams' => 'g',
                'water_ml' => 'ml',
                'sleep_hours' => 'hours',
                default => 'count',
            },
            'position' => fn (array $attributes): int => (int) ForumJournalMeasurement::query()
                ->where('forum_journal_entry_id', $attributes['forum_journal_entry_id'])
                ->max('position') + 1,
        ];
    }
}
