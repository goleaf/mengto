<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumMentorshipEventType;
use App\Enums\ForumMentorshipState;
use App\Models\ForumMentorship;
use App\Models\ForumMentorshipEvent;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumMentorshipEvent>
 */
final class ForumMentorshipEventFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_mentorship_id' => ForumMentorship::factory(),
            'event_type' => ForumMentorshipEventType::Requested,
            'to_state' => ForumMentorshipState::Requested,
            'reason_code' => 'mentorship-requested',
            'summary_translation_key' => 'forum_mentorship.events.requested',
            'metadata' => ['source' => 'factory', 'version' => 1],
            'idempotency_key' => 'factory:event:'.Str::uuid()->toString(),
            'created_at' => now(),
        ];
    }
}
