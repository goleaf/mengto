<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceOpeningState: string
{
    case OpenNow = 'open_now';
    case OpeningSoon = 'opening_soon';
    case Closed = 'closed';
    case StatusUnknown = 'status_unknown';
    case StaleSchedule = 'stale_schedule';
    case TemporarilyClosed = 'temporarily_closed';
}
