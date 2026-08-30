<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceReviewEligibilityContext: string
{
    case Visit = 'visit';
    case Service = 'service';
    case Event = 'event';
    case Other = 'other';
}
