<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumBlock;

/**
 * @extends ApplicationFactory<ForumBlock>
 */
class ForumBlockFactory extends ApplicationFactory
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
