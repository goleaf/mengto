<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ForumCommunityNote;
use App\Models\ForumCommunityNoteVersion;
use App\Models\ForumReviewPanel;
use App\Models\ForumReviewPanelEvent;
use App\Models\User;

final class ForumReviewAudit
{
    /** @param array<string, mixed> $metadata */
    public function panelEvent(
        ForumReviewPanel $panel,
        ?User $actor,
        string $eventType,
        ?string $fromState,
        ?string $toState,
        string $reasonCode,
        array $metadata = [],
        ?string $idempotencyKey = null,
    ): ForumReviewPanelEvent {
        return ForumReviewPanelEvent::query()->create([
            'forum_review_panel_id' => $panel->id,
            'actor_user_id' => $actor?->id,
            'event_type' => $eventType,
            'from_state' => $fromState,
            'to_state' => $toState,
            'reason_code' => $reasonCode,
            'summary_translation_key' => "forum_review.events.{$eventType}",
            'metadata' => $metadata,
            'idempotency_key' => $idempotencyKey,
            'created_at' => now(),
        ]);
    }

    /** @param array<string, mixed> $metadata */
    public function noteVersion(
        ForumCommunityNote $note,
        ?User $editor,
        string $changeReason,
        string $sourceEvent,
        array $metadata = [],
    ): ForumCommunityNoteVersion {
        return ForumCommunityNoteVersion::query()->create([
            'forum_community_note_id' => $note->id,
            'version_number' => $note->current_version,
            'editor_user_id' => $editor?->id,
            'status' => $note->status,
            'body' => $note->body,
            'evidence' => $note->evidence,
            'author_response' => $note->author_response,
            'change_reason' => $changeReason,
            'source_event' => $sourceEvent,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
