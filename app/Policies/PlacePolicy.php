<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PlaceAccessPurpose;
use App\Enums\PlaceStatus;
use App\Enums\PlaceVisibility;
use App\Models\Place;
use App\Models\User;

final class PlacePolicy
{
    public function viewAny(?User $user): bool
    {
        return $user?->isActive() === true && $user->hasVerifiedEmail();
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true && $user->hasVerifiedEmail();
    }

    public function askQuestion(?User $user, Place $place): bool
    {
        return $user?->hasVerifiedEmail() === true && $this->view($user, $place);
    }

    public function view(?User $user, Place $place): bool
    {
        if ($user?->isActive() !== true || $place->status === PlaceStatus::Archived) {
            return false;
        }

        return $place->visibility === PlaceVisibility::Public
            || $place->isManagedBy($user)
            || $place->isVisibleToOrganizationMember($user)
            || $place->activeExactGrantFor($user) !== null;
    }

    public function update(?User $user, Place $place): bool
    {
        return $user?->isActive() === true
            && $place->status !== PlaceStatus::Archived
            && $place->isManagedBy($user);
    }

    public function manageAccess(?User $user, Place $place): bool
    {
        return $this->update($user, $place);
    }

    public function useForEvent(?User $user, Place $place): bool
    {
        if ($user?->isActive() !== true || $place->status !== PlaceStatus::Active) {
            return false;
        }

        return $place->visibility === PlaceVisibility::Public
            || $place->isManagedBy($user)
            || $place->hasActiveExactGrantFor($user, PlaceAccessPurpose::EventOperations);
    }

    public function viewExactLocation(?User $user, Place $place): bool
    {
        return $user?->isActive() === true
            && $place->status === PlaceStatus::Active
            && ($place->isManagedBy($user) || $place->activeExactGrantFor($user) !== null);
    }

    public function delete(?User $user, Place $place): bool
    {
        return false;
    }

    public function restore(?User $user, Place $place): bool
    {
        return false;
    }

    public function forceDelete(?User $user, Place $place): bool
    {
        return false;
    }
}
