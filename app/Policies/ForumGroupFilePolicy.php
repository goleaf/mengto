<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumGroupFileStatus;
use App\Models\ForumGroup;
use App\Models\ForumGroupFile;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class ForumGroupFilePolicy
{
    public function view(User $user, ForumGroupFile $file): bool
    {
        $group = ForumGroup::query()->find($file->forum_group_id);

        return $file->status === ForumGroupFileStatus::Active
            && $group !== null
            && Gate::forUser($user)->allows('viewMemberContent', $group);
    }

    public function create(User $user, ForumGroup $group): bool
    {
        return Gate::forUser($user)->allows('uploadFile', $group);
    }

    public function delete(User $user, ForumGroupFile $file): bool
    {
        $group = ForumGroup::query()->find($file->forum_group_id);

        return $group !== null
            && ($file->uploaded_by_user_id === $user->id
                || Gate::forUser($user)->allows('manageContent', $group));
    }
}
