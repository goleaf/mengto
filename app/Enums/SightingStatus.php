<?php

declare(strict_types=1);

namespace App\Enums;

enum SightingStatus: string
{
    case Submitted = 'submitted';
    case NeedsReview = 'needs-review';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return __("lost_found.sighting_status.{$this->value}");
    }
}
