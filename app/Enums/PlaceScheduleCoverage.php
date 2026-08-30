<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceScheduleCoverage: string
{
    case Complete = 'complete';
    case Partial = 'partial';
}
