<?php

declare(strict_types=1);

namespace App\Enums;

enum ReputationEventStatus: string
{
    case Active = 'active';
    case Reversed = 'reversed';
    case PendingReview = 'pending-review';
    case Rejected = 'rejected';
}
