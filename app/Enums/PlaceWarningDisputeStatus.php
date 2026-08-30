<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceWarningDisputeStatus: string
{
    case Submitted = 'submitted';
    case InReview = 'in_review';
    case Upheld = 'upheld';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
}
