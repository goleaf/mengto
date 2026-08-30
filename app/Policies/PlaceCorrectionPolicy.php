<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PlaceCorrectionStatus;
use App\Models\Place;
use App\Models\PlaceCorrection;
use App\Models\User;

final readonly class PlaceCorrectionPolicy
{
    public function __construct(private PlacePolicy $places) {}

    public function view(?User $user, PlaceCorrection $correction): bool
    {
        return $this->places->view($user, $correction->place);
    }

    public function submit(?User $user, Place $place): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && $this->places->view($user, $place);
    }

    public function review(?User $user, PlaceCorrection $correction): bool
    {
        return $user?->hasVerifiedEmail() === true
            && $this->places->update($user, $correction->place);
    }

    public function withdraw(?User $user, PlaceCorrection $correction): bool
    {
        return $user?->isActive() === true
            && $correction->submitter_user_id === $user->id
            && in_array($correction->moderation_status, [
                PlaceCorrectionStatus::Pending,
                PlaceCorrectionStatus::InReview,
                PlaceCorrectionStatus::NeedsInformation,
            ], true);
    }

    public function report(?User $user, PlaceCorrection $correction): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && $this->view($user, $correction);
    }

    public function delete(?User $user, PlaceCorrection $correction): bool
    {
        return false;
    }

    public function restore(?User $user, PlaceCorrection $correction): bool
    {
        return false;
    }

    public function forceDelete(?User $user, PlaceCorrection $correction): bool
    {
        return false;
    }
}
