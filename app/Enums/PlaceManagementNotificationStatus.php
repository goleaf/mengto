<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceManagementNotificationStatus: string
{
    case Pending = 'pending';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
}
