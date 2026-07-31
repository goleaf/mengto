<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContentPublication;
use App\Models\User;

final class ContentPublicationVisibility
{
    public function __construct(
        private readonly SocialAccountActorQuery $accountActors,
        private readonly SocialBlockService $blocks,
    ) {}

    public function allows(?User $viewer, ContentPublication $publication): bool
    {
        $actorIds = $viewer === null
            ? []
            : $this->accountActors->controlledBy($viewer)->modelKeys();
        $blockedActorIds = $viewer === null
            ? []
            : $this->blocks->blockedActorIdsFor($viewer);

        return ContentPublication::query()
            ->visibleTo($viewer, $actorIds, $blockedActorIds)
            ->whereKey($publication->id)
            ->exists();
    }
}
