<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ForumMentorshipEventType;
use App\Enums\ForumMentorshipState;
use App\Models\ForumMentorship;
use App\Models\ForumMentorshipEvent;
use App\Models\User;

final class MentorshipAudit
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        ForumMentorship $mentorship,
        ?User $actor,
        ForumMentorshipEventType $eventType,
        string $reasonCode,
        string $summaryTranslationKey,
        ?ForumMentorshipState $fromState = null,
        ?ForumMentorshipState $toState = null,
        array $metadata = [],
        ?string $idempotencyKey = null,
    ): ForumMentorshipEvent {
        if ($idempotencyKey !== null) {
            return ForumMentorshipEvent::query()->createOrFirst(
                ['idempotency_key' => $idempotencyKey],
                [
                    'forum_mentorship_id' => $mentorship->id,
                    'actor_user_id' => $actor?->id,
                    'event_type' => $eventType,
                    'from_state' => $fromState,
                    'to_state' => $toState,
                    'reason_code' => $reasonCode,
                    'summary_translation_key' => $summaryTranslationKey,
                    'metadata' => $metadata,
                    'created_at' => now(),
                ],
            );
        }

        return ForumMentorshipEvent::query()->create([
            'forum_mentorship_id' => $mentorship->id,
            'actor_user_id' => $actor?->id,
            'event_type' => $eventType,
            'from_state' => $fromState,
            'to_state' => $toState,
            'reason_code' => $reasonCode,
            'summary_translation_key' => $summaryTranslationKey,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
