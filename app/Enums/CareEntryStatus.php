<?php

namespace App\Enums;

enum CareEntryStatus: string
{
    case Completed = 'completed';
    case Partial = 'partial';
    case InProgress = 'in-progress';
    case Refused = 'refused';
    case Skipped = 'skipped';
    case NeedsHelp = 'needs-help';
    case NeedsReview = 'needs-review';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Completed',
            self::Partial => 'Partially completed',
            self::InProgress => 'In progress',
            self::Refused => 'Pet refused',
            self::Skipped => 'Skipped',
            self::NeedsHelp => 'Needs help',
            self::NeedsReview => 'Needs review',
            self::Cancelled => 'Entered by mistake',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Completed => 'success',
            self::NeedsHelp, self::NeedsReview => 'warning',
            default => 'surface',
        };
    }
}
