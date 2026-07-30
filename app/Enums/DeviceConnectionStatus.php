<?php

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
        return match ($this) {
            self::Online => 'Online',
            self::Offline => 'Offline',
            self::Weak => 'Weak connection',
            self::Connecting => 'Connecting',
            self::Syncing => 'Syncing',
            self::AuthenticationError => 'Sign-in required',
            self::Unsupported => 'Support ended',
        };
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
