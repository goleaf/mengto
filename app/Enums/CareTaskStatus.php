<?php

namespace App\Enums;

enum CareTaskStatus: string
{
    case Planned = 'planned';
    case DueSoon = 'due-soon';
    case Completed = 'completed';
    case Partial = 'partial';
    case Postponed = 'postponed';
    case Missed = 'missed';
    case Cancelled = 'cancelled';
    case Refused = 'refused';
    case NeedsHelp = 'needs-help';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::DueSoon => 'Due soon',
            self::Completed => 'Completed',
            self::Partial => 'Partially completed',
            self::Postponed => 'Postponed',
            self::Missed => 'Missed',
            self::Cancelled => 'Cancelled',
            self::Refused => 'Pet refused',
            self::NeedsHelp => 'Needs help',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [
            self::Planned,
            self::DueSoon,
            self::Postponed,
            self::NeedsHelp,
        ], true);
    }
}
