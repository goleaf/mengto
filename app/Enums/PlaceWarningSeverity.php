<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceWarningSeverity: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public function requiresModeration(): bool
    {
        return in_array($this, [self::High, self::Critical], true);
    }
}
