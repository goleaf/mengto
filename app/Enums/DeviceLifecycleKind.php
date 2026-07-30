<?php

declare(strict_types=1);

namespace App\Enums;

enum DeviceLifecycleKind: string
{
    case Firmware = 'firmware';
    case Maintenance = 'maintenance';
    case Subscription = 'subscription';
    case Recall = 'recall';
    case Vulnerability = 'vulnerability';
    case Theft = 'theft';
    case Transfer = 'transfer';
    case Disposal = 'disposal';

    public function label(): string
    {
        return __("devices.lifecycle.kind.{$this->value}");
    }
}
