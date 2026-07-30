<?php

namespace App\Enums;

enum DeviceConfidence: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::High => 'High confidence',
            self::Medium => 'Medium confidence',
            self::Low => 'Low confidence',
            self::Unknown => 'Confidence unknown',
        };
    }
}
