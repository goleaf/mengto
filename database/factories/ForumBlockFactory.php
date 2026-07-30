<?php

namespace Database\Factories;

use App\Models\ForumBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumBlock>
 */
class ForumBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_key' => fake()->unique()->userName(),
            'blocked_author_key' => fake()->unique()->userName(),
            'reason' => null,
        ];
    }
}
