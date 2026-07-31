<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumTrustLevel;
use App\Models\ForumUserTrustLevel;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumUserTrustLevel>
 */
final class ForumUserTrustLevelFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'forum_trust_level_id' => ForumTrustLevel::factory(),
            'granted_by_user_id' => User::factory()->administrator(),
            'scope_type' => 'global',
            'scope_key' => 'global',
            'reason_code' => 'manual-review',
            'granted_at' => now(),
        ];
    }
}
