<?php

namespace App\Enums;

enum DeviceAutomationStatus: string
{
    case Draft = 'draft';
    case Enabled = 'enabled';
    case Paused = 'paused';
    case Disabled = 'disabled';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Enabled => 'Enabled',
            self::Paused => 'Paused',
            self::Disabled => 'Disabled',
            self::Blocked => 'Blocked by safety check',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Enabled => 'success',
            self::Draft, self::Paused => 'warning',
            self::Blocked => 'danger',
            self::Disabled => 'surface',
        };
    }
}
