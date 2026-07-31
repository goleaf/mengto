<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventMessageAudience;
use App\Models\ForumEvent;
use App\Models\ForumEventMessage;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumEventMessage>
 */
final class ForumEventMessageFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_id' => ForumEvent::factory(),
            'sender_user_id' => User::factory(),
            'stable_key' => 'event-message-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'audience' => ForumEventMessageAudience::Attendees,
            'body' => fake()->sentence(),
        ];
    }

    public function organizersOnly(): static
    {
        return $this->state(fn (): array => [
            'audience' => ForumEventMessageAudience::Organizers,
        ]);
    }
}
