<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PetProfile;
use App\Models\User;

final class PetProfilePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, PetProfile $petProfile): bool
    {
        return $petProfile->status === 'active'
            && ($petProfile->visibility === 'public' || $user?->id === $petProfile->user_id);
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, PetProfile $petProfile): bool
    {
        return $user->isActive() && $user->id === $petProfile->user_id;
    }

    public function delete(User $user, PetProfile $petProfile): bool
    {
        return $this->update($user, $petProfile);
    }

    public function restore(User $user, PetProfile $petProfile): bool
    {
        return $this->update($user, $petProfile);
    }

    public function forceDelete(User $user, PetProfile $petProfile): bool
    {
        return $user->isAdministrator() && $user->id === $petProfile->user_id;
    }
}
