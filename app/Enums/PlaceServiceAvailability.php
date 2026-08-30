<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceServiceAvailability: string
{
    case Available = 'available';
    case TemporarilyUnavailable = 'temporarily_unavailable';
    case Unavailable = 'unavailable';
    case Unknown = 'unknown';
}
