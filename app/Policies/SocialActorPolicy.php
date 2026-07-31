<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SocialActor;
use App\Models\User;
use App\Services\SocialActorAccess;

final class SocialActorPolicy
{
    public function __construct(private readonly SocialActorAccess $access) {}

    public function view(?User $user, SocialActor $actor): bool
    {
        return $this->access->canView($actor, $user);
    }

    public function represent(User $user, SocialActor $actor): bool
    {
        return $user->isActive() && $this->access->canRepresent($actor, $user);
    }

    public function updateSettings(User $user, SocialActor $actor): bool
    {
        return $this->represent($user, $actor);
    }
}
