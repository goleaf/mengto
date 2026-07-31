<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumGroupEventType;
use App\Models\ForumGroup;
use App\Models\ForumGroupEvent;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumGroupEvent>
 */
final class ForumGroupEventFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_group_id' => ForumGroup::factory(),
            'actor_user_id' => User::factory(),
            'subject_user_id' => null,
            'event_type' => ForumGroupEventType::Created,
            'reason_code' => 'factory-event',
            'summary_translation_key' => 'forum_groups.events.created',
            'metadata' => [],
            'idempotency_key' => 'factory:group-event:'.Str::uuid()->toString(),
            'created_at' => now(),
        ];
    }
}
