<?php

namespace App\Enums;

enum SightingStatus: string
{
    case Submitted = 'submitted';
    case NeedsReview = 'needs-review';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Awaiting review',
            self::NeedsReview => 'Needs verification',
            self::Confirmed => 'Confirmed sighting',
            self::Rejected => 'Not a match',
        };
    }
}
