<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Services\ForumActor;

class OrderPolicy
{
    public function __construct(private readonly ForumActor $actor) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Order $order): bool
    {
        return $this->isParticipant($order);
    }

    public function dispute(?User $user, Order $order): bool
    {
        return $this->isParticipant($order);
    }

    public function review(?User $user, Order $order): bool
    {
        return $order->buyer_key === $this->actor->key();
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

    private function isParticipant(Order $order): bool
    {
        return in_array($this->actor->key(), [$order->buyer_key, $order->seller_key], true);
    }
}
