<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceReviewModerationStatus: string
{
    case Pending = 'pending';
    case Published = 'published';
    case Hidden = 'hidden';
    case Removed = 'removed';
}
