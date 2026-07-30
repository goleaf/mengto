<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use App\Services\ForumActor;

class BookingPolicy
{
    public function __construct(private readonly ForumActor $actor) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Booking $booking): bool
    {
        return $booking->client_key === $this->actor->key()
            || $booking->expertProfile()
                ->select(['id', 'owner_key'])
                ->where('owner_key', $this->actor->key())
                ->exists();
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(?User $user, Booking $booking): bool
    {
        return $this->view($user, $booking);
    }

    public function delete(?User $user, Booking $booking): bool
    {
        return false;
    }

    public function restore(?User $user, Booking $booking): bool
    {
        return false;
    }

    public function forceDelete(?User $user, Booking $booking): bool
    {
        return false;
    }
}
