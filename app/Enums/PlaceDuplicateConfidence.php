<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceDuplicateConfidence: string
{
    case Possible = 'possible';
    case Likely = 'likely';
    case Strong = 'strong';
}
