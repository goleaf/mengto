<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ForumReviewPanel;
use App\Models\User;

final class ForumReviewPanelPolicy
{
    public function view(?User $user, ForumReviewPanel $panel): bool
    {
        if ($user?->isActive() !== true) {
            return false;
        }

        return $user->isAdministrator()
            || $panel->requested_by_user_id === $user->id
            || (int) data_get($panel->public_context, 'author_user_id') === $user->id
            || $panel->assignments()
                ->where('reviewer_user_id', $user->id)
                ->exists();
    }

    public function review(?User $user, ForumReviewPanel $panel): bool
    {
        return $user?->isActive() === true
            && $panel->state->isOpen()
            && $panel->assignments()
                ->where('reviewer_user_id', $user->id)
                ->exists();
    }

    public function appeal(?User $user, ForumReviewPanel $panel): bool
    {
        return $user?->isActive() === true
            && (
                $panel->requested_by_user_id === $user->id
                || (int) data_get($panel->public_context, 'author_user_id') === $user->id
            );
    }

    public function moderate(?User $user, ForumReviewPanel $panel): bool
    {
        return $user?->isAdministrator() === true;
    }
}
