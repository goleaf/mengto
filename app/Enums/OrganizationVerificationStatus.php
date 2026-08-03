<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationVerificationStatus: string
{
    case NotAssessed = 'not_assessed';
    case Pending = 'pending';
    case Verified = 'verified';
    case Expired = 'expired';
    case Rejected = 'rejected';
    case Disputed = 'disputed';

    public function label(): string
    {
        return __('organizations.verification_statuses.'.$this->value);
    }
}
