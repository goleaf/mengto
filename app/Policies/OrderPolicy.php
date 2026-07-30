<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function view(?User $user, Order $order): bool
    {
        return $this->isParticipant($user, $order);
    }

    public function dispute(?User $user, Order $order): bool
    {
        return $this->isParticipant($user, $order);
    }

    public function review(?User $user, Order $order): bool
    {
        return $user?->isActive() === true
            && $order->buyer_key === $user->actor_key;
    }

    public function create(?User $user): bool
    {
        return false;
    }

    public function update(?User $user, Order $order): bool
    {
        return false;
    }

    public function delete(?User $user, Order $order): bool
    {
        return false;
    }

    private function isParticipant(?User $user, Order $order): bool
    {
        return $user?->isActive() === true
            && in_array($user->actor_key, [$order->buyer_key, $order->seller_key], true);
    }
}
