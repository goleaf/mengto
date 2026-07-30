<?php

namespace App\Enums;

enum DeviceEventSeverity: string
{
    case Routine = 'routine';
    case Important = 'important';
    case Urgent = 'urgent';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Routine => 'Routine',
            self::Important => 'Important',
            self::Urgent => 'Urgent',
            self::Critical => 'Critical',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Routine => 'surface',
            self::Important => 'warning',
            self::Urgent, self::Critical => 'danger',
        };
    }
}
