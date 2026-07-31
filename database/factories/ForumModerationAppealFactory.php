<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumModerationAction;
use App\Models\ForumModerationAppeal;
use App\Models\User;

/** @extends ApplicationFactory<ForumModerationAppeal> */
final class ForumModerationAppealFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_moderation_action_id' => ForumModerationAction::factory(),
            'appellant_user_id' => User::factory(),
            'status' => 'submitted',
            'reason' => fake()->paragraph(),
            'evidence' => [],
            'submitted_at' => now(),
        ];
    }
}
