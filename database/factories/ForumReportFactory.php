<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumReport;
use App\Models\ForumReportReason;
use App\Models\ForumTopic;
use App\Models\User;

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
        $topic = ForumTopic::factory();

        return [
            'topic_id' => $topic,
            'subject_type' => ForumTopic::class,
            'subject_id' => fn (array $attributes): string => (string) $attributes['topic_id'],
            'reporter_id' => User::factory(),
            'reporter_key' => fake()->unique()->userName(),
            'reason' => 'misinformation',
            'forum_report_reason_id' => ForumReportReason::factory([
                'stable_key' => 'misinformation-'.fake()->unique()->numerify('#####'),
            ]),
            'details' => fake()->sentence(),
            'priority' => 'standard',
            'status' => 'submitted',
            'urgency' => 'standard',
            'contact_preference' => 'platform',
            'immediate_safety' => false,
            'truthfulness_confirmed' => true,
            'deduplication_key' => hash('sha256', fake()->uuid()),
            'idempotency_key' => null,
            'metadata' => [],
        ];
    }
}
