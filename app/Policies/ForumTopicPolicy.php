<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumTopicStatus;
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

        if (! $forumTopic->status->isPubliclyVisible()) {
            return false;
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
        if ($user?->isAdministrator() === true) {
            return true;
        }

        return $user?->isActive() === true
            && $forumTopic->author_key === $user->actor_key
            && ! in_array($this->status($forumTopic)->canonical(), [
                ForumTopicStatus::Merged,
                ForumTopicStatus::Redirected,
                ForumTopicStatus::Removed,
            ], true)
            && ($forumTopic->forum_group_id === null || $this->view($user, $forumTopic));
    }

    public function answer(?User $user, ForumTopic $forumTopic): bool
    {
        return $this->view($user, $forumTopic)
            && $user?->isActive() === true
            && ! $forumTopic->is_locked
            && $this->status($forumTopic)->acceptsAnswers();
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

        return $this->manageOwnedLifecycle($user, $forumTopic)
            && ! $forumTopic->hasActiveLegalHold();
    }

    public function requestUpdate(?User $user, ForumTopic $forumTopic): bool
    {
        return $user?->isActive() === true
            && $forumTopic->author_key !== $user->actor_key
            && $this->view($user, $forumTopic)
            && $this->status($forumTopic)->isPubliclyVisible();
    }

    public function proposeUpdate(?User $user, ForumTopic $forumTopic): bool
    {
        return $this->requestUpdate($user, $forumTopic);
    }

    public function reviewUpdateRequests(?User $user, ForumTopic $forumTopic): bool
    {
        return $user?->isAdministrator() === true
            || $this->manageOwnedLifecycle($user, $forumTopic);
    }

    public function reopen(?User $user, ForumTopic $forumTopic): bool
    {
        return $user?->isAdministrator() === true
            || $this->manageOwnedLifecycle($user, $forumTopic);
    }

    public function bump(?User $user, ForumTopic $forumTopic): bool
    {
        return $this->manageOwnedLifecycle($user, $forumTopic)
            && $this->status($forumTopic)->isPubliclyVisible()
            && ! $forumTopic->hasActiveLegalHold();
    }

    public function archive(?User $user, ForumTopic $forumTopic): bool
    {
        return ($user?->isAdministrator() === true
            || $this->manageOwnedLifecycle($user, $forumTopic))
            && ! $forumTopic->hasActiveLegalHold();
    }

    public function remove(?User $user, ForumTopic $forumTopic): bool
    {
        return $this->delete($user, $forumTopic);
    }

    public function moderateLifecycle(?User $user, ForumTopic $forumTopic): bool
    {
        return $user?->isAdministrator() === true;
    }

    public function manageLegalHold(?User $user, ForumTopic $forumTopic): bool
    {
        return $user?->isAdministrator() === true;
    }

    public function redirect(?User $user, ForumTopic $forumTopic): bool
    {
        return $user?->isAdministrator() === true
            && ! $forumTopic->hasActiveLegalHold();
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
        $required = [
            'id',
            'type',
            'forum_group_id',
            'author_key',
            'visibility',
            'status',
            'legal_hold_at',
        ];

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
        $status = $this->status($forumTopic)->canonical();

        if ($user?->isAdministrator() === true) {
            return in_array($status, [
                ForumTopicStatus::Archived,
                ForumTopicStatus::Merged,
                ForumTopicStatus::Redirected,
                ForumTopicStatus::Removed,
            ], true);
        }

        return $this->manageOwnedLifecycle($user, $forumTopic)
            && in_array($status, [
                ForumTopicStatus::Archived,
                ForumTopicStatus::Removed,
            ], true);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, ForumTopic $forumTopic): bool
    {
        return false;
    }

    private function manageOwnedLifecycle(?User $user, ForumTopic $forumTopic): bool
    {
        return $user?->isActive() === true
            && $forumTopic->author_key === $user->actor_key;
    }

    private function status(ForumTopic $forumTopic): ForumTopicStatus
    {
        $status = $forumTopic->getAttributes()['status']
            ?? ForumTopic::query()->whereKey($forumTopic->id)->value('status');

        return $status instanceof ForumTopicStatus
            ? $status
            : ForumTopicStatus::from((string) $status);
    }
}
