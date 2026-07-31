<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use App\Models\ForumEvent;
use App\Models\ForumEventUpdate;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumEventUpdate>
 */
final class ForumEventUpdateFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_id' => ForumEvent::factory(),
            'author_user_id' => User::factory(),
            'stable_key' => 'event-update-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'type' => ForumEventUpdateType::General,
            'audience' => ForumEventUpdateAudience::Public,
            'title' => fake()->sentence(5),
            'body' => fake()->paragraph(),
            'published_at' => now(),
        ];
    }

    public function attendeesOnly(): static
    {
        return $this->state(fn (): array => [
            'audience' => ForumEventUpdateAudience::Attendees,
        ]);
    }

    public function rescheduled(): static
    {
        return $this->state(fn (): array => [
            'type' => ForumEventUpdateType::Rescheduled,
        ]);
    }
}
