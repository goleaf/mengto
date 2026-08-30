<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Place;
use App\Models\PlaceWarning;
use App\Models\User;

final readonly class PlaceWarningPolicy
{
    public function __construct(private PlacePolicy $places) {}

    public function view(?User $user, PlaceWarning $warning): bool
    {
        if (! $this->places->view($user, $warning->place)) {
            return false;
        }

        return $warning->status->isActive()
            || $this->canManage($user, $warning->place)
            || $user?->id === $warning->author_user_id;
    }

    public function submit(?User $user, Place $place): bool
    {
        return $user?->hasVerifiedEmail() === true && $this->places->view($user, $place);
    }

    public function confirm(?User $user, PlaceWarning $warning): bool
    {
        return $user?->hasVerifiedEmail() === true
            && $this->view($user, $warning)
            && $warning->status->isActive()
            && $warning->expires_at->isFuture();
    }

    public function dispute(?User $user, PlaceWarning $warning): bool
    {
        return $user?->hasVerifiedEmail() === true
            && $warning->status->isActive()
            && $this->canManage($user, $warning->place);
    }

    public function resolve(?User $user, PlaceWarning $warning): bool
    {
        return $user?->hasVerifiedEmail() === true && $this->canManage($user, $warning->place);
    }

    public function appeal(?User $user, PlaceWarning $warning): bool
    {
        return $user?->hasVerifiedEmail() === true
            && in_array($warning->status->value, ['rejected', 'removed', 'resolved'], true)
            && ($user->id === $warning->author_user_id || $this->canManage($user, $warning->place));
    }

    public function delete(?User $user, PlaceWarning $warning): bool
    {
        return false;
    }

    public function restore(?User $user, PlaceWarning $warning): bool
    {
        return false;
    }

    public function forceDelete(?User $user, PlaceWarning $warning): bool
    {
        return false;
    }

    private function canManage(?User $user, Place $place): bool
    {
        return $user?->isAdministrator() === true || $this->places->update($user, $place);
    }
}
