<?php

declare(strict_types=1);

namespace App\Enums;

enum DeviceCommandStatus: string
{
    case Created = 'created';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Accepted = 'accepted';
    case Completed = 'completed';
    case Failed = 'failed';
    case Expired = 'expired';
    case Unknown = 'unknown';

    public function label(): string
    {
        return __("devices.command_status.{$this->value}");
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::Completed,
            self::Failed,
            self::Expired,
            self::Unknown,
        ], true);
    }
}
