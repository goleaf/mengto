<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumEvent;
use App\Models\ForumEventRoom;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumEventRoom> */
final class ForumEventRoomFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_id' => ForumEvent::factory(),
            'stable_key' => 'event-room-'.Str::lower((string) Str::ulid()),
            'name' => fake()->unique()->words(2, true),
            'public_directions' => fake()->sentence(),
            'exact_directions' => fake()->sentence(),
            'online_url' => null,
            'capacity' => fake()->numberBetween(10, 120),
            'accessibility_information' => fake()->sentence(),
            'is_online' => false,
            'is_private' => false,
            'position' => fake()->numberBetween(0, 20),
        ];
    }

    public function online(): static
    {
        return $this->state(fn (): array => [
            'public_directions' => __('forum_events.defaults.online_location'),
            'exact_directions' => null,
            'online_url' => 'https://events.example.test/session/'.Str::lower((string) Str::ulid()),
            'is_online' => true,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (): array => ['is_private' => true]);
    }
}
