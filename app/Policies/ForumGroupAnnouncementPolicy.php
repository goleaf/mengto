<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ForumGroup;
use App\Models\ForumGroupAnnouncement;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class ForumGroupAnnouncementPolicy
{
    public function view(User $user, ForumGroupAnnouncement $announcement): bool
    {
        $group = ForumGroup::query()->find($announcement->forum_group_id);

        return $group !== null
            && Gate::forUser($user)->allows('viewMemberContent', $group);
    }

    public function create(User $user, ForumGroup $group): bool
    {
        return Gate::forUser($user)->allows('publishAnnouncement', $group);
    }

    public function update(User $user, ForumGroupAnnouncement $announcement): bool
    {
        $group = ForumGroup::query()->find($announcement->forum_group_id);

        return $group !== null
            && ($announcement->author_user_id === $user->id
                || Gate::forUser($user)->allows('manageContent', $group));
    }
}
