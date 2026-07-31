<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumJournalCollaboratorRole;
use App\Enums\ForumVisibility;
use App\Models\ForumGroup;
use App\Models\ForumJournal;
use App\Models\User;
use App\Services\ForumProfessionalAccess;
use Illuminate\Contracts\Auth\Access\Gate;

final readonly class ForumJournalPolicy
{
    public function __construct(
        private Gate $gate,
        private ForumProfessionalAccess $professionals,
    ) {}

    public function viewAny(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function view(?User $user, ForumJournal $journal): bool
    {
        $topic = $journal->relationLoaded('topic')
            ? $journal->topic
            : $journal->topic()->first();

        if ($topic === null) {
            return false;
        }

        if ($topic->forum_group_id !== null) {
            if ($user?->isActive() !== true) {
                return false;
            }

            $group = ForumGroup::query()
                ->select([
                    'id',
                    'owner_user_id',
                    'visibility',
                    'status',
                    'active_member_count',
                ])
                ->find($topic->forum_group_id);

            return $group !== null
                && $this->gate->forUser($user)->allows('viewMemberContent', $group);
        }

        $visibilityAllowsAccess = match ($topic->visibility) {
            ForumVisibility::Public,
            ForumVisibility::Link => true,
            ForumVisibility::Members => $user?->isActive() === true,
            ForumVisibility::Experts => false,
            ForumVisibility::Group,
            ForumVisibility::Private => false,
        };

        if ($visibilityAllowsAccess) {
            return true;
        }

        if ($user?->isActive() !== true) {
            return false;
        }

        if ($journal->isOwnedBy($user) || $journal->activeCollaboratorRole($user) !== null) {
            return true;
        }

        return $topic->visibility === ForumVisibility::Experts
            && $this->professionals->allows($user);
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true && $user->hasVerifiedEmail();
    }

    public function update(?User $user, ForumJournal $journal): bool
    {
        if ($user?->isActive() !== true || $journal->isArchived()) {
            return false;
        }

        return $journal->isOwnedBy($user)
            || $journal->activeCollaboratorRole($user)?->canEdit() === true;
    }

    public function comment(?User $user, ForumJournal $journal): bool
    {
        if ($user?->isActive() !== true || $journal->isArchived() || ! $this->view($user, $journal)) {
            return false;
        }

        $topic = $journal->relationLoaded('topic')
            ? $journal->topic
            : $journal->topic()->first();

        if ($topic === null || $topic->is_locked || $topic->comment_policy === 'closed') {
            return false;
        }

        if ($topic->comment_policy === 'experts') {
            return $this->professionals->allows($user);
        }

        return true;
    }

    public function manageCollaborators(?User $user, ForumJournal $journal): bool
    {
        return $user?->isActive() === true
            && ! $journal->isArchived()
            && $journal->isOwnedBy($user);
    }

    public function archive(?User $user, ForumJournal $journal): bool
    {
        return $user?->isActive() === true
            && ! $journal->isArchived()
            && $journal->isOwnedBy($user);
    }

    public function export(?User $user, ForumJournal $journal): bool
    {
        return $user?->isActive() === true
            && (
                $journal->isOwnedBy($user)
                || $journal->activeCollaboratorRole($user) !== null
            );
    }

    public function delete(?User $user, ForumJournal $journal): bool
    {
        return false;
    }

    public function restore(?User $user, ForumJournal $journal): bool
    {
        return false;
    }

    public function forceDelete(?User $user, ForumJournal $journal): bool
    {
        return false;
    }

    public function collaboratorRole(
        ?User $user,
        ForumJournal $journal,
    ): ?ForumJournalCollaboratorRole {
        return $user?->isActive() === true
            ? $journal->activeCollaboratorRole($user)
            : null;
    }
}
