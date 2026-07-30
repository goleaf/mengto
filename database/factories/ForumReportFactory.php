<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumReport;
use App\Models\ForumTopic;

/**
 * @extends ApplicationFactory<ForumReport>
 */
class ForumReportFactory extends ApplicationFactory
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
