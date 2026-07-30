<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumAnswer;
use App\Models\ForumVote;

/**
 * @extends ApplicationFactory<ForumVote>
 */
class ForumVoteFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'answer_id' => ForumAnswer::factory(),
            'user_key' => fake()->unique()->userName(),
            'value' => 'helpful',
            'reason' => null,
        ];
    }
}
