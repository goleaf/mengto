<?php

declare(strict_types=1);

namespace App\Enums;

enum DeviceEventSeverity: string
{
    case Routine = 'routine';
    case Important = 'important';
    case Urgent = 'urgent';
    case Critical = 'critical';

    public function label(): string
    {
        return __("devices.event_severity.{$this->value}");
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
