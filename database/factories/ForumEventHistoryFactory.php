<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumEvent;
use App\Models\ForumEventHistory;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumEventHistory>
 */
final class ForumEventHistoryFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_id' => ForumEvent::factory(),
            'actor_user_id' => User::factory(),
            'subject_user_id' => null,
            'event_type' => 'created',
            'from_status' => null,
            'to_status' => 'scheduled',
            'reason_code' => 'event-created',
            'summary_translation_key' => 'forum_events.history.created',
            'metadata' => null,
            'idempotency_key' => (string) Str::uuid(),
            'created_at' => now(),
        ];
    }
}
