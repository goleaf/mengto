<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumEvent;
use App\Models\ForumEventTrack;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumEventTrack> */
final class ForumEventTrackFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_id' => ForumEvent::factory(),
            'stable_key' => 'event-track-'.Str::lower((string) Str::ulid()),
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'position' => fake()->numberBetween(0, 20),
            'is_public' => true,
        ];
    }

    public function private(): static
    {
        return $this->state(fn (): array => ['is_public' => false]);
    }
}
