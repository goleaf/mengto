<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ForumEvent;
use App\Models\ForumEventHistory;
use App\Models\User;

final class ForumEventAudit
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        ForumEvent $event,
        ?User $actor,
        string $eventType,
        string $reasonCode,
        string $summaryTranslationKey,
        ?User $subject = null,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        array $metadata = [],
        ?string $idempotencyKey = null,
    ): ForumEventHistory {
        $attributes = [
            'forum_event_id' => $event->id,
            'actor_user_id' => $actor?->id,
            'subject_user_id' => $subject?->id,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason_code' => $reasonCode,
            'summary_translation_key' => $summaryTranslationKey,
            'metadata' => $metadata,
            'created_at' => now(),
        ];

        if ($idempotencyKey === null) {
            return ForumEventHistory::query()->create($attributes);
        }

        return ForumEventHistory::query()->createOrFirst(
            ['idempotency_key' => $idempotencyKey],
            $attributes,
        );
    }
}
