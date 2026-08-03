<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventFormat;
use App\Enums\ForumEventStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventOccurrence;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumEventOccurrence> */
final class ForumEventOccurrenceFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $startsAt = now()->addDays(fake()->numberBetween(2, 30))->startOfHour();

        return [
            'forum_event_id' => ForumEvent::factory(),
            'forum_event_series_id' => null,
            'stable_key' => 'event-occurrence-'.Str::lower((string) Str::ulid()),
            'status' => ForumEventStatus::Scheduled,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->clone()->addHours(2),
            'timezone' => 'Europe/Vilnius',
            'format' => ForumEventFormat::Physical,
            'capacity' => 20,
            'location_scope' => fake()->city(),
            'exact_location' => fake()->streetAddress(),
            'online_url' => null,
            'is_override' => false,
            'metadata' => null,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason_code' => 'occurrence-cancelled',
        ]);
    }
}
