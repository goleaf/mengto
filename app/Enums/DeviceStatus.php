<?php

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
        return match ($this) {
            self::Active => 'Active',
            self::NeedsAttention => 'Needs attention',
            self::Maintenance => 'Maintenance',
            self::PrivacyMode => 'Privacy mode',
            self::LostMode => 'Lost mode',
            self::Blocked => 'Blocked',
            self::Retired => 'Retired',
        };
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
