<?php

declare(strict_types=1);

namespace App\Enums;

enum PetEvidenceStatus: string
{
    case Unverified = 'unverified';
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return __("pet_profiles.evidence_statuses.{$this->value}");
    }
}
