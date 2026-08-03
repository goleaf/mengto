<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventRecurrenceFrequency;
use App\Models\ForumEventSeries;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumEventSeries> */
final class ForumEventSeriesFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'owner_user_id' => User::factory(),
            'stable_key' => 'event-series-'.Str::lower((string) Str::ulid()),
            'name' => fake()->sentence(4),
            'frequency' => ForumEventRecurrenceFrequency::Weekly,
            'interval' => 1,
            'weekdays' => [1],
            'timezone' => 'Europe/Vilnius',
            'starts_on' => now()->addWeek()->toDateString(),
            'ends_on' => now()->addMonths(3)->toDateString(),
            'maximum_occurrences' => 12,
            'is_active' => true,
        ];
    }

    public function fixedOccurrences(): static
    {
        return $this->state(fn (): array => [
            'frequency' => ForumEventRecurrenceFrequency::FixedOccurrences,
            'weekdays' => null,
        ]);
    }
}
