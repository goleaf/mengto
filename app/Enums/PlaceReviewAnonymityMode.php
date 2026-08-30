<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceReviewAnonymityMode: string
{
    case Named = 'named';
    case Anonymous = 'anonymous';
}
