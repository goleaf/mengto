<?php

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
        return match ($this) {
            self::Unsubmitted => 'Not submitted',
            self::Submitted => 'Documents submitted',
            self::InReview => 'Under review',
            self::MoreInformation => 'More information needed',
            self::PartiallyVerified => 'Partially verified',
            self::Verified => 'Qualification verified',
            self::Expiring => 'Verification needs renewal',
            self::Suspended => 'Verification paused',
            self::Rejected => 'Documents not accepted',
        };
    }
}
