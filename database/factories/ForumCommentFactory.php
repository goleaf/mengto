<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumComment;
use App\Models\ForumTopic;

/**
 * @extends ApplicationFactory<ForumComment>
 */
class ForumCommentFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic_id' => ForumTopic::factory(),
            'author_key' => fake()->unique()->userName(),
            'author_name' => fake()->name(),
            'author_initials' => fake()->lexify('??'),
            'body' => fake()->sentence(),
            'status' => 'published',
            'is_pinned' => false,
        ];
    }
}
