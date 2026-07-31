<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PhotoReaction;
use App\Models\User;

final class PhotoReactionPolicy
{
    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function update(?User $user, PhotoReaction $reaction): bool
    {
        return $user?->isActive() === true && $reaction->user_id === $user->id;
    }

    public function delete(?User $user, PhotoReaction $reaction): bool
    {
        return $this->update($user, $reaction);
    }
}
