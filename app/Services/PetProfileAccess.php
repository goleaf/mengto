<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetManagerRole;
use App\Enums\PetProfilePermission;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\User;

final class PetProfileAccess
{
    public function canView(PetProfile $profile, ?User $user): bool
    {
        if ($user !== null && $this->allows($profile, $user, PetProfilePermission::View)) {
            return true;
        }

        return $profile->status->isPubliclyEligible()
            && $profile->visibility === 'public'
            && $profile->is_discoverable;
    }

    public function allows(
        PetProfile $profile,
        User $user,
        PetProfilePermission $permission,
    ): bool {
        $manager = $this->membership($profile, $user);

        if ($manager instanceof PetProfileManager) {
            return $manager->allows($permission);
        }

        return $profile->user_id === $user->id
            && ! $this->hasManagerRecord($profile, $user)
            && in_array($permission, PetManagerRole::PrimaryOwner->defaultPermissions(), true);
    }

    public function membership(PetProfile $profile, User $user): ?PetProfileManager
    {
        if ($profile->relationLoaded('managers')) {
            return $profile->managers
                ->first(fn (PetProfileManager $manager): bool => $manager->user_id === $user->id);
        }

        return $profile->managers()
            ->where('user_id', $user->id)
            ->first();
    }

    private function hasManagerRecord(PetProfile $profile, User $user): bool
    {
        if ($profile->relationLoaded('managers')) {
            return $profile->managers
                ->contains(fn (PetProfileManager $manager): bool => $manager->user_id === $user->id);
        }

        return $profile->managers()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function isCriticalStateLocked(PetProfile $profile): bool
    {
        return $profile->status->preventsCriticalChanges();
    }
}
