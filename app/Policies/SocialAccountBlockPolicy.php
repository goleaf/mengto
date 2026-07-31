<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\SocialAccountBlockStatus;
use App\Models\SocialAccountBlock;
use App\Models\User;

final class SocialAccountBlockPolicy
{
    public function view(User $user, SocialAccountBlock $block): bool
    {
        return $user->isActive() && $block->blocker_user_id === $user->id;
    }

    public function revoke(User $user, SocialAccountBlock $block): bool
    {
        return $this->view($user, $block)
            && $block->status === SocialAccountBlockStatus::Active;
    }
}
