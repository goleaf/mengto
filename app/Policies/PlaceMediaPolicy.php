<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ContentMediaStatus;
use App\Enums\PlaceMediaStatus;
use App\Models\PlaceMedia;
use App\Models\User;

final readonly class PlaceMediaPolicy
{
    public function __construct(private PlacePolicy $places) {}

    public function view(?User $user, PlaceMedia $media): bool
    {
        if ($user?->isActive() !== true || ! $user->hasVerifiedEmail()) {
            return false;
        }

        $media->loadMissing(['place.organization.activeMemberships', 'asset']);

        return $media->status === PlaceMediaStatus::Active
            && $media->removed_at === null
            && $media->archived_at === null
            && $media->asset->status === ContentMediaStatus::Ready
            && $media->asset->disk === 'local'
            && $this->places->view($user, $media->place);
    }

    public function update(?User $user, PlaceMedia $media): bool
    {
        if ($user === null) {
            return false;
        }

        $media->loadMissing('place.organization.activeMemberships');

        return $this->places->manageMedia($user, $media->place);
    }
}
