<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\KnowledgeWorkflowEventType;
use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Services\KnowledgeGuideHistory;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;

final class SetKnowledgeEditorialLock
{
    public function __construct(
        private readonly Gate $gate,
        private readonly KnowledgeGuideHistory $history,
    ) {}

    public function handle(
        User $actor,
        KnowledgeArticle $article,
        bool $locked,
        ?string $reason,
    ): KnowledgeArticle {
        $this->gate->forUser($actor)->authorize('manageWorkflow', $article);

        return DB::transaction(function () use (
            $actor,
            $article,
            $locked,
            $reason,
        ): KnowledgeArticle {
            $record = KnowledgeArticle::query()
                ->lockForUpdate()
                ->findOrFail($article->id);

            $record->update([
                'editorial_locked_at' => $locked ? now() : null,
                'editorial_locked_by_user_id' => $locked ? $actor->id : null,
                'editorial_lock_reason' => $locked ? trim((string) $reason) : null,
                'lock_version' => $record->lock_version + 1,
            ]);

            $this->history->record(
                $record,
                $actor,
                $locked
                    ? KnowledgeWorkflowEventType::EditorialLocked
                    : KnowledgeWorkflowEventType::EditorialUnlocked,
                $locked ? 'editorial-locked' : 'editorial-unlocked',
                $locked
                    ? 'knowledge.events.editorial_locked'
                    : 'knowledge.events.editorial_unlocked',
                ['reason' => $locked ? trim((string) $reason) : null],
                versionNumber: $record->current_version,
            );

            return $record->refresh();
        });
    }
}
