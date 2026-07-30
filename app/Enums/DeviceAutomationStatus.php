<?php

declare(strict_types=1);

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
        return __("devices.automation_status.{$this->value}");
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
