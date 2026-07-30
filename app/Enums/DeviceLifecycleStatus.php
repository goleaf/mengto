<?php

declare(strict_types=1);

namespace App\Enums;

enum DeviceLifecycleStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in-progress';
    case Completed = 'completed';
    case Blocked = 'blocked';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __("devices.lifecycle.status.{$this->value}");
    }

    public function isResolved(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }
}
