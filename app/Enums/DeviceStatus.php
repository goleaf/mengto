<?php

declare(strict_types=1);

namespace App\Enums;

enum DeviceStatus: string
{
    case Active = 'active';
    case NeedsAttention = 'needs-attention';
    case Maintenance = 'maintenance';
    case PrivacyMode = 'privacy-mode';
    case LostMode = 'lost-mode';
    case Blocked = 'blocked';
    case Retired = 'retired';

    public function label(): string
    {
        return __("devices.status.{$this->value}");
    }

    public function tone(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::NeedsAttention, self::LostMode => 'warning',
            self::Blocked => 'danger',
            default => 'surface',
        };
    }
}
