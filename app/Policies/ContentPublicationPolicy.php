<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ContentPublication;
use App\Models\User;
use App\Services\ContentPublicationVisibility;
use App\Services\SocialActorAccess;

final class ContentPublicationPolicy
{
    public function __construct(
        private readonly ContentPublicationVisibility $visibility,
        private readonly SocialActorAccess $actorAccess,
    ) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ContentPublication $publication): bool
    {
        if ($user !== null && $this->update($user, $publication)) {
            return true;
        }

        return $this->visibility->allows($user, $publication);
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function update(?User $user, ContentPublication $publication): bool
    {
        if ($user?->isActive() !== true) {
            return false;
        }

        $publication->loadMissing([
            'publishingActor' => fn ($query) => $query->directoryFields(),
        ]);

        return $publication->real_author_user_id === $user->id
            || $this->actorAccess->canPublishAs($publication->publishingActor, $user);
    }

    public function delete(?User $user, ContentPublication $publication): bool
    {
        return $this->update($user, $publication);
    }

    public function restore(?User $user, ContentPublication $publication): bool
    {
        return $this->update($user, $publication);
    }

    public function forceDelete(?User $user, ContentPublication $publication): bool
    {
        return false;
    }
}
