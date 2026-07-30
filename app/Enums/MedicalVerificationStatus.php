<?php

namespace App\Enums;

enum MedicalVerificationStatus: string
{
    case OwnerReported = 'owner-reported';
    case Unverified = 'unverified';
    case NeedsReview = 'needs-review';
    case ProfessionalConfirmed = 'professional-confirmed';
    case OrganizationIssued = 'organization-issued';
    case Superseded = 'superseded';

    public function label(): string
    {
        return match ($this) {
            self::OwnerReported => 'Owner-reported',
            self::Unverified => 'Not verified',
            self::NeedsReview => 'Needs review',
            self::ProfessionalConfirmed => 'Confirmed by veterinarian',
            self::OrganizationIssued => 'Issued by verified organization',
            self::Superseded => 'Superseded record',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::ProfessionalConfirmed, self::OrganizationIssued => 'success',
            self::NeedsReview => 'warning',
            self::Superseded => 'neutral',
            default => 'surface',
        };
    }
}
