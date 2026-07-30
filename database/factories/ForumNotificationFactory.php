<?php

namespace Database\Factories;

use App\Models\ForumNotification;
use App\Models\ForumTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ForumNotification>
 */
class ForumNotificationFactory extends Factory
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
            'type' => 'new-answer',
            'title' => fake()->sentence(),
            'body' => fake()->sentence(),
            'deduplication_key' => Str::uuid()->toString(),
            'read_at' => null,
        ];
    }
}
