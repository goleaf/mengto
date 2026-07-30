<?php

namespace App\Policies;

use App\Models\ForumTopic;
use App\Models\User;

class ForumTopicPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, ForumTopic $forumTopic): bool
    {
        return $forumTopic->visibility->value !== 'private'
            || $forumTopic->author_key === 'mia-carter';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, ForumTopic $forumTopic): bool
    {
        return $forumTopic->author_key === 'mia-carter';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, ForumTopic $forumTopic): bool
    {
        return $forumTopic->author_key === 'mia-carter';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, ForumTopic $forumTopic): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, ForumTopic $forumTopic): bool
    {
        return false;
    }
}
