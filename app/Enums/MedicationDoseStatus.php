<?php

namespace App\Enums;

enum MedicationDoseStatus: string
{
    case Given = 'given';
    case Partial = 'partial';
    case Refused = 'refused';
    case Vomited = 'vomited';
    case Missed = 'missed';
    case Cancelled = 'cancelled';
    case Late = 'late';

    public function label(): string
    {
        return match ($this) {
            self::Given => 'Given',
            self::Partial => 'Partially given',
            self::Refused => 'Pet refused',
            self::Vomited => 'Vomited after dose',
            self::Missed => 'Missed',
            self::Cancelled => 'Cancelled by instruction',
            self::Late => 'Given late',
        };
    }

    public function preventsRepeat(): bool
    {
        return in_array($this, [self::Given, self::Partial, self::Late], true);
    }
}
