<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumBadge;
use App\Models\ForumUserBadge;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumUserBadge>
 */
final class ForumUserBadgeFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'forum_badge_id' => ForumBadge::factory(),
            'granted_by_user_id' => User::factory()->administrator(),
            'scope_key' => 'global',
            'status' => 'active',
            'reason_code' => 'criteria-met',
            'is_public' => true,
            'granted_at' => now(),
            'metadata' => [],
        ];
    }
}
