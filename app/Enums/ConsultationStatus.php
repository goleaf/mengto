<?php

namespace App\Enums;

enum ConsultationStatus: string
{
    case Scheduled = 'scheduled';
    case InProgress = 'in-progress';
    case SummaryPending = 'summary-pending';
    case FollowUp = 'follow-up';
    case ReferralNeeded = 'referral-needed';
    case Completed = 'completed';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }
}
