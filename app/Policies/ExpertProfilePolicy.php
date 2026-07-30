<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ExpertProfileStatus;
use App\Models\ExpertProfile;
use App\Models\User;

class ExpertProfilePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ExpertProfile $expertProfile): bool
    {
        return $expertProfile->status === ExpertProfileStatus::Published
            || ($user?->isActive() === true && $expertProfile->owner_key === $user->actor_key);
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function update(?User $user, ExpertProfile $expertProfile): bool
    {
        return $user?->isActive() === true
            && $expertProfile->owner_key === $user->actor_key;
    }

    public function delete(?User $user, ExpertProfile $expertProfile): bool
    {
        return $this->update($user, $expertProfile);
    }

    public function restore(?User $user, ExpertProfile $expertProfile): bool
    {
        return false;
    }

    public function forceDelete(?User $user, ExpertProfile $expertProfile): bool
    {
        return false;
    }
}
