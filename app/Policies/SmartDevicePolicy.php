<?php

namespace App\Policies;

use App\Models\SmartDevice;
use App\Models\User;
use App\Services\ForumActor;

class SmartDevicePolicy
{
    public function __construct(private readonly ForumActor $actor) {}

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
    public function view(?User $user, SmartDevice $smartDevice): bool
    {
        return $smartDevice->isOwnedBy($this->actor->key());
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, SmartDevice $smartDevice): bool
    {
        return $this->view($user, $smartDevice);
    }

    public function control(?User $user, SmartDevice $smartDevice): bool
    {
        return $this->view($user, $smartDevice)
            && ! $smartDevice->is_blocked
            && ! $smartDevice->is_reported_stolen;
    }

    public function share(?User $user, SmartDevice $smartDevice): bool
    {
        return $this->view($user, $smartDevice);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, SmartDevice $smartDevice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, SmartDevice $smartDevice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, SmartDevice $smartDevice): bool
    {
        return false;
    }
}
