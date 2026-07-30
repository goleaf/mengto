<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case AwaitingPayment = 'awaiting-payment';
    case Confirmed = 'confirmed';
    case Waitlisted = 'waitlisted';
    case NeedsInformation = 'needs-information';
    case RescheduleProposed = 'reschedule-proposed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Declined = 'declined';
    case NoShow = 'no-show';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }
}
