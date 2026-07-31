<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ListingStatus;
use App\Enums\ModerationStatus;
use App\Models\AdoptionCase;
use App\Models\Listing;
use App\Models\User;

final class AdoptionCasePolicy
{
    public function view(?User $user, AdoptionCase $case): bool
    {
        $listing = $this->listing($case);

        return $this->manage($user, $case)
            || (
                $listing->status === ListingStatus::Published
                && $listing->moderation_status === ModerationStatus::Approved
            );
    }

    public function apply(User $user, AdoptionCase $case): bool
    {
        if (! $user->isActive() || ! $user->hasVerifiedEmail() || ! $case->status->acceptsApplications()) {
            return false;
        }

        $listing = $this->listing($case);

        return $listing->owner_key !== $user->actor_key
            && $listing->status === ListingStatus::Published
            && $listing->moderation_status === ModerationStatus::Approved;
    }

    public function manage(?User $user, AdoptionCase $case): bool
    {
        if ($user?->isActive() !== true) {
            return false;
        }

        return $user->isAdministrator()
            || $this->listing($case)->owner_key === $user->actor_key;
    }

    private function listing(AdoptionCase $case): Listing
    {
        if ($case->relationLoaded('listing')) {
            return $case->listing;
        }

        return Listing::query()
            ->select(['id', 'owner_key', 'status', 'moderation_status'])
            ->findOrFail($case->listing_id);
    }
}
