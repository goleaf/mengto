<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use App\Models\ForumGroup;
use App\Models\SocialActor;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

final readonly class CommunityMembershipActorEligibility
{
    public function __construct(
        private SocialActorResolver $actors,
        private SocialActorAccess $access,
    ) {}

    /** @return Collection<int, SocialActor> */
    public function availableTo(User $user, ForumGroup $group): Collection
    {
        return $this->actors
            ->controlledBy($user)
            ->filter(fn (SocialActor $actor): bool => $this->groupAllows($group, $actor))
            ->values();
    }

    public function resolveFor(
        User $user,
        ForumGroup $group,
        string $actorKey,
    ): SocialActor {
        $actor = $this->availableTo($user, $group)
            ->firstWhere('actor_key', $actorKey);

        if (! $actor instanceof SocialActor) {
            throw new AuthorizationException;
        }

        return $actor;
    }

    public function defaultFor(User $user, ForumGroup $group): SocialActor
    {
        $personalActor = $this->actors->forUser($user);

        if ($this->allows($user, $group, $personalActor)) {
            return $personalActor;
        }

        $actor = $this->availableTo($user, $group)->first();

        if (! $actor instanceof SocialActor) {
            throw new AuthorizationException;
        }

        return $actor;
    }

    public function allows(User $user, ForumGroup $group, SocialActor $actor): bool
    {
        $actor->refresh();

        return $this->groupAllows($group, $actor)
            && $this->access->canRepresent($actor, $user);
    }

    private function groupAllows(ForumGroup $group, SocialActor $actor): bool
    {
        return $actor->status === SocialActorStatus::Active
            && $actor->actor_type !== SocialActorType::Group
            && in_array($actor->actor_type->value, $group->allowedActorTypes(), true);
    }
}
