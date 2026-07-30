<?php

namespace Database\Factories;

use App\Models\ForumAnswer;
use App\Models\ForumTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumAnswer>
 */
class ForumAnswerFactory extends Factory
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
            'author_role' => 'Experienced pet owner',
            'body' => fake()->paragraphs(2, true),
            'experience_type' => 'personal-experience',
            'is_verified_expert' => false,
            'sources' => [],
            'media' => [],
            'status' => 'published',
            'is_accepted' => false,
            'is_highlighted' => false,
            'needs_source' => false,
            'helpful_count' => 0,
        ];
    }

    public function expert(): static
    {
        return $this->state(fn (): array => [
            'author_role' => 'Verified veterinarian',
            'experience_type' => 'professional-opinion',
            'is_verified_expert' => true,
            'expertise' => 'Companion animal medicine',
            'qualification_region' => 'Lithuania',
        ]);
    }
}
