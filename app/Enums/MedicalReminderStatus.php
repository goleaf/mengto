<?php

namespace App\Enums;

enum MedicalReminderStatus: string
{
    case Scheduled = 'scheduled';
    case Snoozed = 'snoozed';
    case Completed = 'completed';
    case Missed = 'missed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::Snoozed => 'Snoozed',
            self::Completed => 'Completed',
            self::Missed => 'Missed',
            self::Cancelled => 'Cancelled',
        };
    }
}
