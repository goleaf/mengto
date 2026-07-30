<?php

declare(strict_types=1);

namespace App\Enums;

enum DeviceConnectionStatus: string
{
    case Online = 'online';
    case Offline = 'offline';
    case Weak = 'weak';
    case Connecting = 'connecting';
    case Syncing = 'syncing';
    case AuthenticationError = 'authentication-error';
    case Unsupported = 'unsupported';

    public function label(): string
    {
        return __("devices.connection_status.{$this->value}");
    }

    public function tone(): string
    {
        return match ($this) {
            self::Online => 'success',
            self::Weak, self::Connecting, self::Syncing => 'warning',
            self::Offline, self::AuthenticationError, self::Unsupported => 'danger',
        };
    }
}
