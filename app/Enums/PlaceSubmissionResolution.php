<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceSubmissionResolution: string
{
    case None = 'none';
    case NewPlace = 'new-place';
    case ExistingLink = 'existing-link';
    case DuplicateMerge = 'duplicate-merge';
}
