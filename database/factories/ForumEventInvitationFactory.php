<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventInvitationStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventInvitation;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumEventInvitation>
 */
final class ForumEventInvitationFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_id' => ForumEvent::factory(),
            'invited_by_user_id' => User::factory(),
            'invited_user_id' => User::factory(),
            'stable_key' => 'event-invitation-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'status' => ForumEventInvitationStatus::Pending,
            'expires_at' => now()->addWeeks(2),
            'responded_at' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventInvitationStatus::Accepted,
            'responded_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventInvitationStatus::Expired,
            'expires_at' => now()->subDay(),
            'responded_at' => now()->subDay(),
        ]);
    }
}
