<?php

namespace App\Enums;

enum DisputeStatus: string
{
    case Open = 'open';
    case NeedsEvidence = 'needs-evidence';
    case UnderReview = 'under-review';
    case Resolved = 'resolved';
    case Rejected = 'rejected';
    case Appealed = 'appealed';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }
}
