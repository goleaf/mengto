<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ForumGroupEventType;
use App\Models\ForumGroup;
use App\Models\ForumGroupEvent;
use App\Models\User;

final class ForumGroupAudit
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        ForumGroup $group,
        ?User $actor,
        ForumGroupEventType $eventType,
        string $reasonCode,
        string $summaryTranslationKey,
        ?User $subject = null,
        array $metadata = [],
        ?string $idempotencyKey = null,
    ): ForumGroupEvent {
        $attributes = [
            'forum_group_id' => $group->id,
            'actor_user_id' => $actor?->id,
            'subject_user_id' => $subject?->id,
            'event_type' => $eventType,
            'reason_code' => $reasonCode,
            'summary_translation_key' => $summaryTranslationKey,
            'metadata' => $metadata,
            'created_at' => now(),
        ];

        if ($idempotencyKey === null) {
            return ForumGroupEvent::query()->create($attributes);
        }

        return ForumGroupEvent::query()->firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            $attributes,
        );
    }
}
