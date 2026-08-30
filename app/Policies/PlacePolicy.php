<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\PlaceAccessPurpose;
use App\Enums\PlaceStatus;
use App\Enums\PlaceVisibility;
use App\Models\OrganizationMembership;
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
        if ($user?->isActive() !== true || in_array($place->status, [PlaceStatus::Archived, PlaceStatus::Merged], true)) {
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
            && ! in_array($place->status, [PlaceStatus::Archived, PlaceStatus::Merged], true)
            && $place->isManagedBy($user);
    }

    public function submitManagementClaim(?User $user, Place $place): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && $place->status === PlaceStatus::Active
            && $place->archived_at === null
            && $this->view($user, $place);
    }

    public function manageAccess(?User $user, Place $place): bool
    {
        return $this->update($user, $place);
    }

    public function manageMedia(?User $user, Place $place): bool
    {
        return $this->update($user, $place);
    }

    public function moderateMedia(?User $user, Place $place): bool
    {
        return $user?->isAdministrator() === true
            && $user->hasVerifiedEmail()
            && ! in_array($place->status, [PlaceStatus::Archived, PlaceStatus::Merged], true);
    }

    public function report(?User $user, Place $place): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && $this->view($user, $place);
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

    public function discloseMergedIdentifier(
        ?User $user,
        Place $place,
        ?PlaceVisibility $visibilityCeiling = null,
    ): bool {
        if ($user?->isActive() !== true || ! $user->hasVerifiedEmail()) {
            return false;
        }

        if (($visibilityCeiling === null || $visibilityCeiling === PlaceVisibility::Public)
            && $place->visibility === PlaceVisibility::Public
            || $user->isAdministrator()
            || $place->owner_user_id === $user->id) {
            return true;
        }

        return $place->organization_id !== null
            && OrganizationMembership::query()
                ->active()
                ->where('organization_id', $place->organization_id)
                ->where('user_id', $user->id)
                ->whereIn('role', OrganizationRole::placeManagerValues())
                ->whereHas('organization', static fn ($query) => $query
                    ->where('status', OrganizationStatus::Active->value)
                    ->whereNull('archived_at'))
                ->exists();
    }
}
