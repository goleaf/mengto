<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;

class ListingPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Listing $listing): bool
    {
        return ($user?->isActive() === true && $listing->owner_key === $user->actor_key)
            || in_array($listing->status, [
                ListingStatus::Published,
                ListingStatus::Reserved,
                ListingStatus::Completed,
            ], true);
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function update(?User $user, Listing $listing): bool
    {
        return $user?->isActive() === true
            && $listing->owner_key === $user->actor_key;
    }

    public function delete(?User $user, Listing $listing): bool
    {
        return $this->update($user, $listing);
    }

    public function reserve(?User $user, Listing $listing): bool
    {
        return $user?->isActive() === true
            && $listing->owner_key !== $user->actor_key
            && $listing->status === ListingStatus::Published;
    }

    public function cancelReservation(?User $user, Listing $listing, Reservation $reservation): bool
    {
        return $reservation->listing_id === $listing->id
            && $user?->isActive() === true
            && $reservation->requester_key === $user->actor_key;
    }

    public function restore(?User $user, Listing $listing): bool
    {
        return false;
    }

    public function forceDelete(?User $user, Listing $listing): bool
    {
        return false;
    }
}
