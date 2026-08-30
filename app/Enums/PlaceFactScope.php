<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceFactScope: string
{
    case Submitted = 'submitted';
    case Published = 'published';
    case Linked = 'linked';
    case Merged = 'merged';
}
