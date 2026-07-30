<?php

namespace Database\Factories;

use App\Enums\ForumSubscriptionLevel;
use App\Models\ForumEngagement;
use App\Models\ForumTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumEngagement>
 */
class ForumEngagementFactory extends Factory
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
            'user_key' => fake()->unique()->userName(),
            'is_bookmarked' => fake()->boolean(),
            'subscription_level' => ForumSubscriptionLevel::None,
            'last_read_at' => now(),
        ];
    }
}
