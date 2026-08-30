<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceQuestionStatus: string
{
    case Open = 'open';
    case Answered = 'answered';
    case NeedsInformation = 'needs_information';
    case Duplicate = 'duplicate';
    case Hidden = 'hidden';
    case Closed = 'closed';
    case Removed = 'removed';
}
