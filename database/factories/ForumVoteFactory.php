<?php

namespace Database\Factories;

use App\Models\ForumAnswer;
use App\Models\ForumVote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumVote>
 */
class ForumVoteFactory extends Factory
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
