<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ForumExpertSession;
use App\Models\ForumExpertSessionAnswer;
use App\Models\ForumExpertSessionHistory;
use App\Models\ForumExpertSessionQuestion;
use App\Models\User;

final class ForumExpertSessionAudit
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        ForumExpertSession $session,
        ?User $actor,
        string $eventType,
        string $reasonCode,
        string $summaryTranslationKey,
        ?ForumExpertSessionQuestion $question = null,
        ?ForumExpertSessionAnswer $answer = null,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        array $metadata = [],
        ?string $idempotencyKey = null,
    ): ForumExpertSessionHistory {
        $attributes = [
            'forum_expert_session_id' => $session->id,
            'forum_expert_session_question_id' => $question?->id,
            'forum_expert_session_answer_id' => $answer?->id,
            'actor_user_id' => $actor?->id,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason_code' => $reasonCode,
            'summary_translation_key' => $summaryTranslationKey,
            'metadata' => $metadata,
            'created_at' => now(),
        ];

        if ($idempotencyKey === null) {
            return ForumExpertSessionHistory::query()->create($attributes);
        }

        return ForumExpertSessionHistory::query()->createOrFirst(
            ['idempotency_key' => $idempotencyKey],
            $attributes,
        );
    }
}
