<?php

namespace Database\Factories;

use App\Models\ForumReport;
use App\Models\ForumTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumReport>
 */
class ForumReportFactory extends Factory
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
            'reporter_key' => fake()->unique()->userName(),
            'reason' => 'misinformation',
            'details' => fake()->sentence(),
            'priority' => 'normal',
            'status' => 'new',
        ];
    }
}
