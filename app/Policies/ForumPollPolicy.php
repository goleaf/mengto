<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumPollVoterVisibility;
use App\Models\ForumGroup;
use App\Models\ForumPoll;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class ForumPollPolicy
{
    public function view(User $user, ForumPoll $poll): bool
    {
        return $this->canViewGroup($user, $poll->forum_group_id);
    }

    public function create(User $user, ForumGroup $group): bool
    {
        return Gate::forUser($user)->allows('createPoll', $group);
    }

    public function vote(User $user, ForumPoll $poll): bool
    {
        return $user->isActive()
            && ! $poll->isClosed()
            && $this->view($user, $poll);
    }

    public function viewResults(User $user, ForumPoll $poll): bool
    {
        return $this->view($user, $poll)
            && $poll->resultsAreVisibleTo($user);
    }

    public function viewVoters(User $user, ForumPoll $poll): bool
    {
        return $poll->voter_visibility === ForumPollVoterVisibility::Visible
            && $this->viewResults($user, $poll);
    }

    private function canViewGroup(User $user, int $groupId): bool
    {
        $group = ForumGroup::query()->find($groupId);

        return $group !== null
            && Gate::forUser($user)->allows('viewMemberContent', $group);
    }
}
