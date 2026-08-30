<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceManagementClaimStatus: string
{
    case Pending = 'pending';
    case NeedsInformation = 'needs_information';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Revoked = 'revoked';
    case Superseded = 'superseded';

    public function label(): string
    {
        return __('places.management.statuses.'.$this->value);
    }

    public function retainsActiveConflict(): bool
    {
        return in_array($this, [
            self::Pending,
            self::NeedsInformation,
            self::UnderReview,
            self::Approved,
        ], true);
    }
}
