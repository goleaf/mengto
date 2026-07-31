<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ForumGroup;
use App\Models\ForumGroupActivity;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class ForumGroupActivityPolicy
{
    public function view(User $user, ForumGroupActivity $activity): bool
    {
        $group = ForumGroup::query()->find($activity->forum_group_id);

        return $group !== null
            && Gate::forUser($user)->allows('viewMemberContent', $group);
    }

    public function create(User $user, ForumGroup $group): bool
    {
        return Gate::forUser($user)->allows('createContent', $group);
    }

    public function update(User $user, ForumGroupActivity $activity): bool
    {
        $group = ForumGroup::query()->find($activity->forum_group_id);

        return $group !== null
            && ($activity->created_by_user_id === $user->id
                || Gate::forUser($user)->allows('manageContent', $group));
    }
}
