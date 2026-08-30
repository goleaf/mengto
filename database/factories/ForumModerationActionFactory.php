<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumModerationAction;
use App\Models\ForumModerationActionDefinition;
use App\Models\ForumModerationCase;
use App\Models\User;

/** @extends ApplicationFactory<ForumModerationAction> */
final class ForumModerationActionFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_moderation_case_id' => ForumModerationCase::factory(),
            'forum_moderation_action_definition_id' => ForumModerationActionDefinition::factory(),
            'actor_user_id' => User::factory()->administrator(),
            'target_user_id' => User::factory(),
            'rule_id' => 'community-safety-001',
            'policy_basis' => 'community-safety',
            'scope_type' => 'global',
            'scope_key' => 'global',
            'user_reason_translation_key' => 'forum_moderation.messages.action_applied',
            'internal_reason' => fake()->sentence(),
            'evidence' => [['type' => 'moderation-context', 'reference' => 'factory-case']],
            'starts_at' => now(),
            'appeal_available' => true,
            'metadata' => ['source' => 'factory', 'version' => 1],
        ];
    }
}
