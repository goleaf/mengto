<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PlaceManagementClaimStatus;
use App\Models\PlaceManagementClaim;
use App\Models\PlaceManagementReviewer;
use App\Models\User;

final class PlaceManagementClaimPolicy
{
    public function view(?User $user, PlaceManagementClaim $claim): bool
    {
        if ($user?->isActive() !== true || ! $user->hasVerifiedEmail()) {
            return false;
        }

        return $claim->claimant_user_id === $user->id
            || $claim->reviewer_user_id === $user->id
            || $user->isAdministrator();
    }

    public function addEvidence(?User $user, PlaceManagementClaim $claim): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && $claim->claimant_user_id === $user->id
            && in_array($claim->status, [
                PlaceManagementClaimStatus::Pending,
                PlaceManagementClaimStatus::NeedsInformation,
            ], true)
            && ($claim->expires_at === null || $claim->expires_at->isFuture());
    }

    public function downloadEvidence(?User $user, PlaceManagementClaim $claim): bool
    {
        if ($user?->isActive() !== true || ! $user->hasVerifiedEmail()) {
            return false;
        }

        if ($claim->claimant_user_id === $user->id) {
            return true;
        }

        if ($claim->reviewer_user_id !== $user->id) {
            return false;
        }

        return PlaceManagementReviewer::query()
            ->current()
            ->where('user_id', $user->id)
            ->exists()
            && ! $claim->reviewerRecusals()->where('reviewer_user_id', $user->id)->exists();
    }
}
