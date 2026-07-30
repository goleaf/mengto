<?php

namespace App\Policies;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ForumActor;

class ListingPolicy
{
    public function __construct(private readonly ForumActor $actor) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Listing $listing): bool
    {
        return $listing->owner_key === $this->actor->key()
            || in_array($listing->status, [
                ListingStatus::Published,
                ListingStatus::Reserved,
                ListingStatus::Completed,
            ], true);
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(?User $user, Listing $listing): bool
    {
        return $listing->owner_key === $this->actor->key();
    }

    public function delete(?User $user, Listing $listing): bool
    {
        return $listing->owner_key === $this->actor->key();
    }

    public function reserve(?User $user, Listing $listing): bool
    {
        return $listing->owner_key !== $this->actor->key()
            && $listing->status === ListingStatus::Published;
    }

    public function cancelReservation(?User $user, Listing $listing, Reservation $reservation): bool
    {
        return $reservation->listing_id === $listing->id
            && $reservation->requester_key === $this->actor->key();
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
