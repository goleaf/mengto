<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumConfirmation;
use App\Models\ForumConfirmationVote;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumConfirmationVote>
 */
final class ForumConfirmationVoteFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_confirmation_id' => ForumConfirmation::factory(),
            'voter_user_id' => User::factory(),
            'stance' => 'support',
            'weight' => 1,
            'has_conflict' => false,
            'independence_cluster' => fake()->uuid(),
            'reasoning' => fake()->sentence(),
            'status' => 'eligible',
        ];
    }
}
