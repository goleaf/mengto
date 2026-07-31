<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialRequestStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Hidden = 'hidden';
    case Blocked = 'blocked';
    case RemovedAfterReport = 'removed-after-report';

    public function isOpen(): bool
    {
        return in_array($this, [self::Sent, self::Delivered, self::Pending], true);
    }

    public function label(): string
    {
        return __("social_relationships.request_statuses.{$this->value}");
    }
}
