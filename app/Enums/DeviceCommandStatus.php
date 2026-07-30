<?php

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
        return match ($this) {
            self::Created => 'Created',
            self::Sent => 'Sent',
            self::Delivered => 'Delivered',
            self::Accepted => 'Accepted',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Expired => 'Expired',
            self::Unknown => 'Result unknown',
        };
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
