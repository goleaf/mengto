<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumGroupMembership>
 */
final class ForumGroupMembershipFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_group_id' => ForumGroup::factory(),
            'user_id' => User::factory(),
            'role' => ForumGroupRole::Member,
            'state' => ForumGroupMembershipState::Active,
            'notification_level' => 'important',
            'answers' => [],
            'joined_at' => now(),
            'lock_version' => 0,
            'last_idempotency_key' => 'factory:membership:'.Str::uuid()->toString(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ForumGroupMembership $membership): void {
            $membership->group->forceFill([
                'active_member_count' => $membership->group->memberships()
                    ->where('state', ForumGroupMembershipState::Active->value)
                    ->count(),
            ])->save();
        });
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'state' => ForumGroupMembershipState::Pending,
            'requested_at' => now(),
            'joined_at' => null,
        ]);
    }

    public function banned(): static
    {
        return $this->state(fn (): array => [
            'state' => ForumGroupMembershipState::Banned,
            'restriction_reason' => fake()->sentence(),
            'ended_at' => now(),
            'joined_at' => null,
        ]);
    }

    public function restricted(): static
    {
        return $this->state(fn (): array => [
            'role' => ForumGroupRole::RestrictedMember,
        ]);
    }
}
