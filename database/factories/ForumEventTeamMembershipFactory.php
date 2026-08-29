<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventTeamMembershipStatus;
use App\Enums\ForumEventTeamRole;
use App\Models\ForumEvent;
use App\Models\ForumEventTeamMembership;
use App\Models\User;

/** @extends ApplicationFactory<ForumEventTeamMembership> */
final class ForumEventTeamMembershipFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_id' => ForumEvent::factory(),
            'user_id' => User::factory(),
            'invited_by_user_id' => null,
            'role' => ForumEventTeamRole::CoOrganizer,
            'status' => ForumEventTeamMembershipStatus::Active,
            'permission_overrides' => null,
            'starts_at' => now(),
            'ends_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(static function (ForumEventTeamMembership $membership): void {
            if ($membership->forum_event_id !== null) {
                $membership->invited_by_user_id = ForumEvent::query()
                    ->whereKey($membership->forum_event_id)
                    ->value('organizer_user_id');
            }
        });
    }

    public function revoked(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventTeamMembershipStatus::Revoked,
            'ends_at' => now(),
        ]);
    }
}
