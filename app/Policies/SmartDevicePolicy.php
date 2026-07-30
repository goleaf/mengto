<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SmartDevice;
use App\Models\User;

class SmartDevicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, SmartDevice $smartDevice): bool
    {
        return $user?->isActive() === true
            && $smartDevice->isOwnedBy($user->actor_key);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
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

    public function controlCommand(
        ?User $user,
        SmartDevice $smartDevice,
        string $command,
    ): bool {
        if ($command === 'enable-lost-mode') {
            return $this->view($user, $smartDevice)
                && ! $smartDevice->is_blocked;
        }

        return $this->control($user, $smartDevice);
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
