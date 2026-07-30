<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;

class ConsultationPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function view(?User $user, Consultation $consultation): bool
    {
        return $user?->isActive() === true
            && ($consultation->booking()
                ->select(['id', 'client_key'])
                ->where('client_key', $user->actor_key)
                ->exists()
            || $consultation->expertProfile()
                ->select(['id', 'owner_key'])
                ->where('owner_key', $user->actor_key)
                ->exists());
    }

    public function create(?User $user): bool
    {
        return false;
    }

    public function update(?User $user, Consultation $consultation): bool
    {
        return $user?->isActive() === true
            && $consultation->expertProfile()
                ->select(['id', 'owner_key'])
                ->where('owner_key', $user->actor_key)
                ->exists();
    }

    public function delete(?User $user, Consultation $consultation): bool
    {
        return false;
    }

    public function restore(?User $user, Consultation $consultation): bool
    {
        return false;
    }

    public function forceDelete(?User $user, Consultation $consultation): bool
    {
        return false;
    }
}
