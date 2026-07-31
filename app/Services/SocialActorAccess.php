<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetProfilePermission;
use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use App\Models\ExpertProfile;
use App\Models\ForumGroup;
use App\Models\PetProfile;
use App\Models\SocialActor;
use App\Models\User;

final class SocialActorAccess
{
    public function __construct(private readonly PetProfileAccess $petAccess) {}

    public function canRepresent(SocialActor $actor, User $user): bool
    {
        if ($actor->status !== SocialActorStatus::Active) {
            return false;
        }

        return match ($actor->actor_type) {
            SocialActorType::User => $actor->user_id === $user->id,
            SocialActorType::Pet => $this->canRepresentPet($actor, $user),
            SocialActorType::Expert => $this->canRepresentExpert($actor, $user),
            SocialActorType::Group => $this->canRepresentGroup($actor, $user),
        };
    }

    public function canView(SocialActor $actor, ?User $user): bool
    {
        if ($user !== null && $this->canRepresent($actor, $user)) {
            return true;
        }

        return $actor->status === SocialActorStatus::Active && $actor->is_discoverable;
    }

    public function primaryControllerUserId(SocialActor $actor): ?int
    {
        return match ($actor->actor_type) {
            SocialActorType::User => $actor->user_id,
            SocialActorType::Pet => $this->pet($actor)?->user_id,
            SocialActorType::Expert => $this->expert($actor)?->owner_id,
            SocialActorType::Group => $this->group($actor)?->owner_user_id,
        };
    }

    private function canRepresentPet(SocialActor $actor, User $user): bool
    {
        $profile = $this->pet($actor);

        return $profile instanceof PetProfile
            && $this->petAccess->allows($profile, $user, PetProfilePermission::ManageSocial);
    }

    private function canRepresentExpert(SocialActor $actor, User $user): bool
    {
        return $this->expert($actor)?->owner_id === $user->id;
    }

    private function canRepresentGroup(SocialActor $actor, User $user): bool
    {
        return $this->group($actor)?->owner_user_id === $user->id;
    }

    private function pet(SocialActor $actor): ?PetProfile
    {
        $actor->loadMissing('petProfile');

        return $actor->petProfile;
    }

    private function expert(SocialActor $actor): ?ExpertProfile
    {
        $actor->loadMissing('expertProfile');

        return $actor->expertProfile;
    }

    private function group(SocialActor $actor): ?ForumGroup
    {
        $actor->loadMissing('forumGroup');

        return $actor->forumGroup;
    }
}
