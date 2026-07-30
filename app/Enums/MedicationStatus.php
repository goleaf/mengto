<?php

namespace App\Enums;

enum MedicationStatus: string
{
    case Planned = 'planned';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NeedsReview = 'needs-review';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::Active => 'Active',
            self::Paused => 'Paused by specialist',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::NeedsReview => 'Needs clarification',
        };
    }

    public function isDoseable(): bool
    {
        return $this === self::Active;
    }
}
