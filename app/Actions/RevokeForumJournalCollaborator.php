<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumJournalCollaboratorState;
use App\Models\ForumJournal;
use App\Models\ForumJournalCollaborator;
use App\Models\User;
use App\Services\ForumJournalAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;

final readonly class RevokeForumJournalCollaborator
{
    public function __construct(
        private Gate $gate,
        private ForumJournalAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumJournal $journal,
        ForumJournalCollaborator $collaborator,
    ): ForumJournalCollaborator {
        $this->gate->forUser($actor)->authorize('manageCollaborators', $journal);

        return DB::transaction(function () use ($actor, $collaborator, $journal): ForumJournalCollaborator {
            $lockedJournal = ForumJournal::query()
                ->lockForUpdate()
                ->findOrFail($journal->id);
            $this->gate->forUser($actor)->authorize('manageCollaborators', $lockedJournal);
            $lockedCollaborator = ForumJournalCollaborator::query()
                ->where('forum_journal_id', $lockedJournal->id)
                ->lockForUpdate()
                ->findOrFail($collaborator->id);

            if ($lockedCollaborator->state === ForumJournalCollaboratorState::Revoked) {
                return $lockedCollaborator;
            }

            $lockedCollaborator->forceFill([
                'state' => ForumJournalCollaboratorState::Revoked,
                'revoked_at' => now(),
            ])->save();
            $lockedJournal->increment('lock_version');

            $this->audit->record(
                $lockedJournal,
                $actor,
                'forum-journal.collaborator-revoked',
                ['collaborator_user_id' => $lockedCollaborator->user_id],
            );

            return $lockedCollaborator->refresh();
        }, 3);
    }
}
