<?php

declare(strict_types=1);

namespace App\Enums;

enum PetProfileAccessRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return __("pet_profiles.access_requests.statuses.{$this->value}");
    }
}
