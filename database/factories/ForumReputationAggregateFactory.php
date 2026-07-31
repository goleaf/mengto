<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumReputationAggregate;
use App\Models\ForumReputationDimension;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumReputationAggregate>
 */
final class ForumReputationAggregateFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'forum_reputation_dimension_id' => ForumReputationDimension::factory(),
            'scope_key' => hash('sha256', fake()->unique()->uuid()),
            'total' => fake()->numberBetween(0, 100),
            'last_event_at' => now(),
        ];
    }
}
