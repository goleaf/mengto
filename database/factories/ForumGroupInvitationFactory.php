<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumGroupInvitationState;
use App\Enums\ForumGroupRole;
use App\Models\ForumGroup;
use App\Models\ForumGroupInvitation;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumGroupInvitation>
 */
final class ForumGroupInvitationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::lower((string) Str::ulid());

        return [
            'forum_group_id' => ForumGroup::factory()->private(),
            'invited_user_id' => User::factory(),
            'invited_by_user_id' => null,
            'role' => ForumGroupRole::Member,
            'state' => ForumGroupInvitationState::Pending,
            'message' => fake()->sentence(),
            'open_key' => "factory:invite:open:{$key}",
            'idempotency_key' => "factory:invite:{$key}",
            'expires_at' => now()->addWeek(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(static function (ForumGroupInvitation $invitation): void {
            if ($invitation->forum_group_id !== null) {
                $invitation->invited_by_user_id = ForumGroup::query()
                    ->whereKey($invitation->forum_group_id)
                    ->value('owner_user_id');
            }
        });
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'expires_at' => now()->subMinute(),
        ]);
    }
}
