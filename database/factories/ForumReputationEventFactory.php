<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReputationEventStatus;
use App\Models\ForumReputationDimension;
use App\Models\ForumReputationEvent;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumReputationEvent>
 */
final class ForumReputationEventFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'forum_reputation_dimension_id' => ForumReputationDimension::factory(),
            'actor_user_id' => User::factory(),
            'event_type' => 'helpful-answer',
            'source_entity_type' => 'forum-answer',
            'source_entity_id' => (string) fake()->numberBetween(1, 100_000),
            'amount' => 1,
            'reason_code' => 'helpful-vote',
            'explanation_translation_key' => 'forum.reputation.events.helpful_vote',
            'status' => ReputationEventStatus::Active,
            'idempotency_key' => (string) Str::ulid(),
            'metadata' => [],
            'effective_at' => now(),
        ];
    }
}
