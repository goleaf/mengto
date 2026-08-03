<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PetProfilePermission;
use App\Enums\PetProfileStatus;
use App\Models\PetProfile;
use App\Models\User;
use App\Services\PetProfileAccess;

final class PetProfilePolicy
{
    public function __construct(private readonly PetProfileAccess $access) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, PetProfile $petProfile): bool
    {
        return $this->access->canView($petProfile, $user);
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, PetProfile $petProfile): bool
    {
        return $user->isActive()
            && $this->access->allows(
                $petProfile,
                $user,
                PetProfilePermission::EditBasics,
            );
    }

    public function delete(User $user, PetProfile $petProfile): bool
    {
        return $user->isActive()
            && ! $this->access->isCriticalStateLocked($petProfile)
            && $this->access->allows(
                $petProfile,
                $user,
                PetProfilePermission::DeleteProfile,
            );
    }

    public function restore(User $user, PetProfile $petProfile): bool
    {
        return $user->isActive()
            && $this->access->allows(
                $petProfile,
                $user,
                PetProfilePermission::DeleteProfile,
            );
    }

    public function forceDelete(User $user, PetProfile $petProfile): bool
    {
        return $user->isAdministrator()
            && ! $this->access->isCriticalStateLocked($petProfile)
            && $this->access->allows(
                $petProfile,
                $user,
                PetProfilePermission::DeleteProfile,
            );
    }

    public function managePrivacy(User $user, PetProfile $petProfile): bool
    {
        return $user->isActive()
            && $this->access->allows(
                $petProfile,
                $user,
                PetProfilePermission::ManagePrivacy,
            );
    }

    public function manageManagers(User $user, PetProfile $petProfile): bool
    {
        return $user->isActive()
            && ! $this->access->isCriticalStateLocked($petProfile)
            && $this->access->allows(
                $petProfile,
                $user,
                PetProfilePermission::ManageManagers,
            );
    }

    public function manageMedia(User $user, PetProfile $petProfile): bool
    {
        return $user->isActive()
            && $this->access->allows(
                $petProfile,
                $user,
                PetProfilePermission::ManageMedia,
            );
    }

    public function recordFact(
        User $user,
        PetProfile $petProfile,
        string $factKey,
    ): bool {
        if (! $user->isActive()) {
            return false;
        }

        $permission = in_array($factKey, [
            'microchip-status',
            'microchip-identifier',
            'microchip-record',
            'registration-identifier',
        ], true)
            ? PetProfilePermission::ChangeMicrochip
            : PetProfilePermission::EditBasics;

        return $this->access->allows($petProfile, $user, $permission);
    }

    public function transition(
        User $user,
        PetProfile $petProfile,
        PetProfileStatus $target,
    ): bool {
        if (! $user->isActive()) {
            return false;
        }

        $permission = match ($target) {
            PetProfileStatus::Memorial => PetProfilePermission::ActivateMemorial,
            PetProfileStatus::DeletionPending => PetProfilePermission::DeleteProfile,
            PetProfileStatus::Transferred => PetProfilePermission::TransferOwnership,
            PetProfileStatus::Merged,
            PetProfileStatus::DisputedOwnership => null,
            default => PetProfilePermission::EditBasics,
        };

        return $permission === null
            ? $user->isAdministrator()
            : $this->access->allows($petProfile, $user, $permission);
    }
}
