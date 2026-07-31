<?php

declare(strict_types=1);

namespace App\Enums;

enum VerificationStatus: string
{
    case Unsubmitted = 'unsubmitted';
    case Submitted = 'submitted';
    case InReview = 'in-review';
    case MoreInformation = 'more-information';
    case PartiallyVerified = 'partially-verified';
    case Verified = 'verified';
    case Expiring = 'expiring';
    case Suspended = 'suspended';
    case Rejected = 'rejected';

    public function label(): string
    {
        return __("credential_verification.profile_status.{$this->value}");
    }
}
