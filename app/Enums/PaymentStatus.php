<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case NotRequired = 'not-required';
    case Pending = 'pending';
    case Processing = 'processing';
    case Paid = 'paid';
    case PartiallyPaid = 'partially-paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially-refunded';
    case Disputed = 'disputed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }
}
