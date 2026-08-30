<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceWarningAppealStatus: string
{
    case Submitted = 'submitted';
    case InReview = 'in_review';
    case Upheld = 'upheld';
    case Denied = 'denied';
    case Withdrawn = 'withdrawn';
}
