<?php

namespace App\Policies;

use App\Enums\ExpertProfileStatus;
use App\Models\ExpertProfile;
use App\Models\User;
use App\Services\ForumActor;

class ExpertProfilePolicy
{
    public function __construct(private readonly ForumActor $actor) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ExpertProfile $expertProfile): bool
    {
        return $expertProfile->status === ExpertProfileStatus::Published
            || $expertProfile->owner_key === $this->actor->key();
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(?User $user, ExpertProfile $expertProfile): bool
    {
        return $expertProfile->owner_key === $this->actor->key();
    }

    public function delete(?User $user, ExpertProfile $expertProfile): bool
    {
        return $expertProfile->owner_key === $this->actor->key();
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
