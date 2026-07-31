<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumTopicType;
use App\Enums\ForumVisibility;
use App\Models\ForumGroup;
use App\Models\ForumTopic;
use App\Models\User;
use App\Services\ForumProfessionalAccess;
use Illuminate\Support\Facades\Gate;

class ForumTopicPolicy
{
    public function __construct(
        private readonly ForumJournalPolicy $journals,
        private readonly ForumProfessionalAccess $professionals,
    ) {}

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
        $forumTopic = $this->viewProjection($forumTopic);

        if ($forumTopic === null) {
            return false;
        }

        if ($this->isJournal($forumTopic)) {
            $journal = $forumTopic->journal()
                ->with('topic')
                ->first();

            if ($journal !== null) {
                return $this->journals->view($user, $journal);
            }
        }

        if ($forumTopic->forum_group_id !== null) {
            if ($user?->isActive() !== true) {
                return false;
            }

            $group = ForumGroup::query()->find($forumTopic->forum_group_id);

            return $group !== null
                && Gate::forUser($user)->allows('viewMemberContent', $group);
        }

        if ($user?->isActive() === true && $forumTopic->author_key === $user->actor_key) {
            return true;
        }

        return match ($forumTopic->visibility) {
            ForumVisibility::Public,
            ForumVisibility::Link => true,
            ForumVisibility::Members => $user?->isActive() === true,
            ForumVisibility::Experts => $user instanceof User
                && $this->professionals->allows($user),
            ForumVisibility::Group,
            ForumVisibility::Private => false,
        };
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, ForumTopic $forumTopic): bool
    {
        return $user?->isActive() === true
            && $forumTopic->author_key === $user->actor_key
            && ($forumTopic->forum_group_id === null || $this->view($user, $forumTopic));
    }

    public function answer(?User $user, ForumTopic $forumTopic): bool
    {
        return $this->view($user, $forumTopic)
            && $user?->isActive() === true
            && ! $forumTopic->is_locked;
    }

    public function comment(?User $user, ForumTopic $forumTopic): bool
    {
        return $this->answer($user, $forumTopic)
            && $forumTopic->comment_policy !== 'closed';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, ForumTopic $forumTopic): bool
    {
        if ($this->isJournal($forumTopic)) {
            return false;
        }

        return $this->update($user, $forumTopic);
    }

    private function isJournal(ForumTopic $forumTopic): bool
    {
        $type = $forumTopic->getAttributes()['type']
            ?? ForumTopic::query()->whereKey($forumTopic->id)->value('type');

        return $type === ForumTopicType::Journal->value;
    }

    private function viewProjection(ForumTopic $forumTopic): ?ForumTopic
    {
        $attributes = $forumTopic->getAttributes();
        $required = ['id', 'type', 'forum_group_id', 'author_key', 'visibility'];

        if (array_diff($required, array_keys($attributes)) === []) {
            return $forumTopic;
        }

        return ForumTopic::query()
            ->select($required)
            ->find($forumTopic->id);
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
