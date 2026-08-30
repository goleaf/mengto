<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceWarningStatus: string
{
    case NeedsReview = 'needs_review';
    case Published = 'published';
    case Disputed = 'disputed';
    case Resolved = 'resolved';
    case Expired = 'expired';
    case Rejected = 'rejected';
    case Removed = 'removed';

    public function isActive(): bool
    {
        return in_array($this, [self::Published, self::Disputed], true);
    }
}
