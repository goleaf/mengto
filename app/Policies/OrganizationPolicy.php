<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

final class OrganizationPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user?->isActive() === true && $user->hasVerifiedEmail();
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true && $user->hasVerifiedEmail();
    }

    public function view(?User $user, Organization $organization): bool
    {
        return $user?->isActive() === true
            && ($user->isAdministrator() || $organization->membershipFor($user) !== null);
    }

    public function update(?User $user, Organization $organization): bool
    {
        $membership = $user === null ? null : $organization->membershipFor($user);

        return $user?->isActive() === true
            && ($user->isAdministrator() || $membership?->role->canManageMembers() === true)
            && $organization->archived_at === null;
    }

    public function manageMembers(?User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }

    public function manageRestrictions(?User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }

    public function organizeEvents(?User $user, Organization $organization): bool
    {
        $membership = $user === null ? null : $organization->membershipFor($user);

        return $user?->isActive() === true
            && $organization->isActive()
            && ($user->isAdministrator() || $membership?->role->canManageEvents() === true);
    }

    public function manageFinance(?User $user, Organization $organization): bool
    {
        $membership = $user === null ? null : $organization->membershipFor($user);

        return $user?->isActive() === true
            && $organization->archived_at === null
            && ($user->isAdministrator() || $membership?->role->canManageFinance() === true);
    }

    public function manageSafety(?User $user, Organization $organization): bool
    {
        $membership = $user === null ? null : $organization->membershipFor($user);

        return $user?->isActive() === true
            && $organization->archived_at === null
            && ($user->isAdministrator() || $membership?->role->canManageSafety() === true);
    }

    public function manageMarketplace(?User $user, Organization $organization): bool
    {
        $membership = $user === null ? null : $organization->membershipFor($user);

        return $user?->isActive() === true
            && $organization->archived_at === null
            && ($user->isAdministrator() || $membership?->role->canManageMarketplace() === true);
    }

    public function manageShelter(?User $user, Organization $organization): bool
    {
        $membership = $user === null ? null : $organization->membershipFor($user);

        return $user?->isActive() === true
            && $organization->archived_at === null
            && ($user->isAdministrator() || $membership?->role->canManageShelter() === true);
    }

    public function viewAudit(?User $user, Organization $organization): bool
    {
        $membership = $user === null ? null : $organization->membershipFor($user);

        return $user?->isActive() === true
            && $organization->archived_at === null
            && ($user->isAdministrator() || $membership?->role->canViewAudit() === true);
    }

    public function delete(?User $user, Organization $organization): bool
    {
        return false;
    }

    public function restore(?User $user, Organization $organization): bool
    {
        return false;
    }

    public function forceDelete(?User $user, Organization $organization): bool
    {
        return false;
    }
}
