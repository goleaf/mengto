<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumModerationCase;
use App\Models\ForumModeratorRecusal;
use App\Models\User;

/** @extends ApplicationFactory<ForumModeratorRecusal> */
final class ForumModeratorRecusalFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_moderation_case_id' => ForumModerationCase::factory(),
            'moderator_user_id' => User::factory()->administrator(),
            'reason_code' => 'prior-public-dispute',
            'private_note' => fake()->sentence(),
            'created_at' => now(),
        ];
    }
}
