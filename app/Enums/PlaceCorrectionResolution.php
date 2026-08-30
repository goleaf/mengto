<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceCorrectionResolution: string
{
    case Applied = 'applied';
    case PartiallyApplied = 'partially_applied';
    case NotApplied = 'not_applied';
    case StaleConflict = 'stale_conflict';
    case Withdrawn = 'withdrawn';
    case Superseded = 'superseded';
}
