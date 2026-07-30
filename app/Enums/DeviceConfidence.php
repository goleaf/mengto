<?php

declare(strict_types=1);

namespace App\Enums;

enum DeviceConfidence: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
    case Unknown = 'unknown';

    public function label(): string
    {
        return __("devices.confidence.{$this->value}");
    }
}
