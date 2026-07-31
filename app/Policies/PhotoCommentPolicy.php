<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PhotoComment;
use App\Models\User;

final class PhotoCommentPolicy
{
    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function delete(?User $user, PhotoComment $comment): bool
    {
        return $user?->isActive() === true && $comment->user_id === $user->id;
    }
}
