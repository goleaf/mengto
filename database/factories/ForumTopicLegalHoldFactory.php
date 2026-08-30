<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumTopic;
use App\Models\ForumTopicLegalHold;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumTopicLegalHold>
 */
final class ForumTopicLegalHoldFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_topic_id' => ForumTopic::factory(),
            'applied_by_user_id' => User::factory()->administrator(),
            'reason_code' => 'retention-review',
            'private_reason' => fake()->paragraph(),
            'starts_at' => now(),
            'review_at' => now()->addMonth(),
            'released_at' => null,
            'released_by_user_id' => null,
            'release_reason' => null,
            'active_key' => 'factory-topic-hold-'.Str::uuid(),
            'metadata' => ['source' => 'factory', 'version' => 1],
        ];
    }

    public function released(): static
    {
        return $this->state(fn (): array => [
            'released_at' => now(),
            'released_by_user_id' => User::factory()->administrator(),
            'release_reason' => fake()->paragraph(),
            'active_key' => null,
        ]);
    }
}
