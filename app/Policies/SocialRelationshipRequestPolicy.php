<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SocialRelationshipRequest;
use App\Models\User;
use App\Services\SocialActorAccess;

final class SocialRelationshipRequestPolicy
{
    public function __construct(private readonly SocialActorAccess $access) {}

    public function view(User $user, SocialRelationshipRequest $request): bool
    {
        $request->loadMissing(['sourceActor', 'targetActor']);

        return $this->access->canRepresent($request->sourceActor, $user)
            || $this->access->canRepresent($request->targetActor, $user);
    }

    public function respond(User $user, SocialRelationshipRequest $request): bool
    {
        $request->loadMissing('targetActor');

        return $user->isActive()
            && $this->access->canRepresent($request->targetActor, $user);
    }

    public function cancel(User $user, SocialRelationshipRequest $request): bool
    {
        $request->loadMissing('sourceActor');

        return $user->isActive()
            && $this->access->canRepresent($request->sourceActor, $user);
    }

    public function report(User $user, SocialRelationshipRequest $request): bool
    {
        $request->loadMissing('targetActor');

        return $user->isActive()
            && $request->status->isOpen()
            && $this->access->canRepresent($request->targetActor, $user);
    }
}
