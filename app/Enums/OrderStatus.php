<?php

namespace App\Enums;

enum OrderStatus: string
{
    case AwaitingPayment = 'awaiting-payment';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case InTransit = 'in-transit';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case ReturnRequested = 'return-requested';
    case Returned = 'returned';
    case Disputed = 'disputed';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }
}
