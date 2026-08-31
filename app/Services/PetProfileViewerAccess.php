<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetProfilePermission;
use App\Models\PetProfile;
use App\Models\SocialActor;
use App\Models\User;

final readonly class PetProfileViewerAccess
{
    public function __construct(
        private PetProfileAccess $profiles,
        private SocialBlockService $blocks,
    ) {}

    public function canView(PetProfile $profile, ?User $viewer): bool
    {
        if ($viewer instanceof User
            && $this->profiles->allows($profile, $viewer, PetProfilePermission::View)
        ) {
            return true;
        }

        if (! $this->profiles->canView($profile, null)) {
            return false;
        }

        if (! $viewer instanceof User) {
            return true;
        }

        $actor = $profile->socialActor()->first();

        if (! $actor instanceof SocialActor) {
            return ! $this->blocks->accountBlockedBetween(
                [$viewer->id],
                [$profile->user_id],
            );
        }

        return ! $this->blocks->blockedForContact($viewer, $actor)
            && ! $this->blocks->actorBlockedForContact($viewer, $actor);
    }
}
