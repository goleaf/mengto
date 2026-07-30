<?php

namespace Database\Factories;

use App\Models\ForumComment;
use App\Models\ForumTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumComment>
 */
class ForumCommentFactory extends Factory
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
