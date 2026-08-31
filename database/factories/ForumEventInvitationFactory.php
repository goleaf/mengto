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
            'invited_by_user_id' => null,
            'invited_user_id' => User::factory(),
            'stable_key' => 'event-invitation-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'status' => ForumEventInvitationStatus::Pending,
            'expires_at' => now()->addWeeks(2),
            'responded_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(static function (ForumEventInvitation $invitation): void {
            if ($invitation->forum_event_id !== null) {
                $invitation->invited_by_user_id = ForumEvent::query()
                    ->whereKey($invitation->forum_event_id)
                    ->value('organizer_user_id');
            }

            if ($invitation->forum_event_id !== null && $invitation->invited_user_id !== null) {
                $invitation->active_pair_key = in_array($invitation->status, [
                    ForumEventInvitationStatus::Pending,
                    ForumEventInvitationStatus::Accepted,
                ], true) && $invitation->expires_at->isFuture()
                    ? hash('sha256', $invitation->forum_event_id.'|'.$invitation->invited_user_id)
                    : null;
                $invitation->request_checksum = hash('sha256', json_encode([
                    'event_id' => $invitation->forum_event_id,
                    'inviter_id' => $invitation->invited_by_user_id,
                    'recipient_id' => $invitation->invited_user_id,
                    'expires_at' => $invitation->expires_at->toISOString(),
                    'idempotency_key' => $invitation->idempotency_key,
                ], JSON_THROW_ON_ERROR));
            }
        });
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
