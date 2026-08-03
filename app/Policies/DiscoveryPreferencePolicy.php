<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DiscoveryPreference;
use App\Models\User;

final class DiscoveryPreferencePolicy
{
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isActive();
    }

    public function delete(User $user, DiscoveryPreference $preference): bool
    {
        return $user->isActive() && $preference->user_id === $user->id;
    }
}
