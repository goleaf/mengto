<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceScheduleExceptionKind: string
{
    case FullClosure = 'full_closure';
    case SpecialOpening = 'special_opening';
}
