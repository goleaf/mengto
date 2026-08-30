<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceSpeciesEligibility: string
{
    case Supported = 'supported';
    case NotSupported = 'not_supported';
    case Unknown = 'unknown';
}
