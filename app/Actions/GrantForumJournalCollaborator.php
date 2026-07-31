<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumJournalCollaboratorRole;
use App\Enums\ForumJournalCollaboratorState;
use App\Enums\UserStatus;
use App\Models\ForumJournal;
use App\Models\ForumJournalCollaborator;
use App\Models\User;
use App\Services\ForumJournalAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class GrantForumJournalCollaborator
{
    public function __construct(
        private Gate $gate,
        private ForumJournalAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumJournal $journal,
        string $email,
        ForumJournalCollaboratorRole $role,
    ): ForumJournalCollaborator {
        $this->gate->forUser($actor)->authorize('manageCollaborators', $journal);
        $normalizedEmail = Str::lower(trim($email));
        Validator::make([
            'email' => $normalizedEmail,
            'role' => $role->value,
        ], [
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::exists('users', 'email')->where('status', UserStatus::Active->value),
            ],
            'role' => ['required', Rule::enum(ForumJournalCollaboratorRole::class)],
        ])->validate();
        $collaboratorUser = User::query()
            ->select(['id', 'actor_key', 'name', 'email', 'status'])
            ->where('email', $normalizedEmail)
            ->firstOrFail();

        if ($journal->isOwnedBy($collaboratorUser)) {
            throw ValidationException::withMessages([
                'collaboratorForm.email' => __('forum_journals.validation.owner_collaborator'),
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $collaboratorUser,
            $journal,
            $role,
        ): ForumJournalCollaborator {
            $lockedJournal = ForumJournal::query()
                ->lockForUpdate()
                ->findOrFail($journal->id);
            $this->gate->forUser($actor)->authorize('manageCollaborators', $lockedJournal);
            $collaborator = ForumJournalCollaborator::query()->updateOrCreate(
                [
                    'forum_journal_id' => $lockedJournal->id,
                    'user_id' => $collaboratorUser->id,
                ],
                [
                    'granted_by_user_id' => $actor->id,
                    'role' => $role,
                    'state' => ForumJournalCollaboratorState::Active,
                    'granted_at' => now(),
                    'revoked_at' => null,
                ],
            );
            $lockedJournal->increment('lock_version');

            $this->audit->record(
                $lockedJournal,
                $actor,
                'forum-journal.collaborator-granted',
                [
                    'collaborator_user_id' => $collaboratorUser->id,
                    'role' => $role->value,
                ],
            );

            return $collaborator->load('user:id,name');
        }, 3);
    }
}
