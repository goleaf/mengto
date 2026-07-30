<?php

namespace App\Enums;

enum SearchTaskStatus: string
{
    case Open = 'open';
    case Claimed = 'claimed';
    case InProgress = 'in-progress';
    case Completed = 'completed';
    case NeedsHelp = 'needs-help';
    case Failed = 'failed';
    case Inaccessible = 'inaccessible';
    case Dangerous = 'dangerous';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }

    public function canBeClaimed(): bool
    {
        return in_array($this, [self::Open, self::NeedsHelp], true);
    }
}
