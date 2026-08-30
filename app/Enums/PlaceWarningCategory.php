<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceWarningCategory: string
{
    case Access = 'access';
    case AnimalHealth = 'animal_health';
    case Closure = 'closure';
    case Contamination = 'contamination';
    case Crowding = 'crowding';
    case Hazard = 'hazard';
    case Other = 'other';
}
