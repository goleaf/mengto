<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventSessionRole;
use App\Models\ForumEventSession;
use App\Models\ForumEventSessionStaff;
use App\Models\User;

/** @extends ApplicationFactory<ForumEventSessionStaff> */
final class ForumEventSessionStaffFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_session_id' => ForumEventSession::factory(),
            'user_id' => User::factory(),
            'role' => function (array $attributes): ForumEventSessionRole {
                $used = ForumEventSessionStaff::query()
                    ->where('forum_event_session_id', $attributes['forum_event_session_id'])
                    ->where('user_id', $attributes['user_id'])
                    ->pluck('role');

                return collect(ForumEventSessionRole::cases())
                    ->first(static fn (ForumEventSessionRole $role): bool => ! $used->contains($role->value))
                    ?? ForumEventSessionRole::Staff;
            },
            'is_public' => true,
        ];
    }

    public function private(): static
    {
        return $this->state(fn (): array => ['is_public' => false]);
    }
}
