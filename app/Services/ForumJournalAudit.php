<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ForumJournal;
use App\Models\User;

final class ForumJournalAudit
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        ForumJournal $journal,
        User $actor,
        string $action,
        array $metadata = [],
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_key' => $actor->actor_key,
            'actor_role' => $journal->isOwnedBy($actor)
                ? 'forum-journal-owner'
                : 'forum-journal-collaborator',
            'action' => $action,
            'target_type' => ForumJournal::class,
            'target_id' => (string) $journal->id,
            'metadata' => [
                'forum_topic_id' => $journal->forum_topic_id,
                ...$metadata,
            ],
        ]);
    }
}
