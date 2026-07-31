<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumTopicLifecycleEventType;
use App\Enums\ForumTopicStatus;
use App\Models\ForumTopic;
use App\Models\ForumTopicLifecycleEvent;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumTopicLifecycleEvent>
 */
final class ForumTopicLifecycleEventFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_topic_id' => ForumTopic::factory(),
            'actor_user_id' => User::factory(),
            'event_type' => ForumTopicLifecycleEventType::StateChanged,
            'from_status' => ForumTopicStatus::Published->value,
            'to_status' => ForumTopicStatus::Open->value,
            'reason_code' => 'factory-state-change',
            'reason_translation_key' => null,
            'lock_version' => 2,
            'idempotency_key' => 'factory-topic-event-'.Str::uuid(),
            'metadata' => [],
            'occurred_at' => now(),
        ];
    }
}
