<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumTrustHistory;
use App\Models\ForumTrustLevel;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumTrustHistory>
 */
final class ForumTrustHistoryFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'to_forum_trust_level_id' => ForumTrustLevel::factory(),
            'actor_user_id' => User::factory()->administrator(),
            'scope_type' => 'global',
            'scope_key' => 'global',
            'reason_code' => 'manual-review',
            'evidence' => [],
        ];
    }
}
