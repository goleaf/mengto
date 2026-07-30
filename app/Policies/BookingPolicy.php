<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function view(?User $user, Booking $booking): bool
    {
        return $user?->isActive() === true
            && ($booking->client_key === $user->actor_key
            || $booking->expertProfile()
                ->select(['id', 'owner_key'])
                ->where('owner_key', $user->actor_key)
                ->exists());
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
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
