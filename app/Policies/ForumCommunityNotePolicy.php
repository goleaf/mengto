<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ForumCommunityNote;
use App\Models\User;

final class ForumCommunityNotePolicy
{
    public function view(?User $user, ForumCommunityNote $note): bool
    {
        if ($note->status->isPublic()) {
            return true;
        }

        if ($user?->isActive() !== true) {
            return false;
        }

        return $user->isAdministrator()
            || $note->proposer_user_id === $user->id
            || $note->subject_author_user_id === $user->id
            || $note->reviewPanel?->assignments()
                ->where('reviewer_user_id', $user->id)
                ->exists() === true;
    }

    public function update(?User $user, ForumCommunityNote $note): bool
    {
        return $user?->isActive() === true
            && ! $note->status->isPublic()
            && $note->status->isOpen()
            && $note->proposer_user_id === $user->id;
    }

    public function respond(?User $user, ForumCommunityNote $note): bool
    {
        return $user?->isActive() === true
            && $note->subject_author_user_id === $user->id;
    }

    public function moderate(?User $user, ForumCommunityNote $note): bool
    {
        return $user?->isAdministrator() === true;
    }

    public function delete(?User $user, ForumCommunityNote $note): bool
    {
        return false;
    }
}
