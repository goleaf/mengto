<?php

declare(strict_types=1);

namespace App\Enums;

enum PetProfileStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case FosterCare = 'foster-care';
    case Shelter = 'shelter';
    case SeekingHome = 'seeking-home';
    case AdoptionInProgress = 'adoption-in-progress';
    case Transferred = 'transferred';
    case Lost = 'lost';
    case Found = 'found';
    case IdentityUnverified = 'identity-unverified';
    case DisputedOwnership = 'disputed-ownership';
    case Hidden = 'hidden';
    case Memorial = 'memorial';
    case Merged = 'merged';
    case DeletionPending = 'deletion-pending';
    case Archived = 'archived';

    public function label(): string
    {
        return __("pet_profiles.statuses.{$this->value}");
    }

    public function isPubliclyEligible(): bool
    {
        return in_array($this, [
            self::Active,
            self::FosterCare,
            self::Shelter,
            self::SeekingHome,
            self::AdoptionInProgress,
            self::Lost,
            self::Found,
            self::Memorial,
        ], true);
    }

    public function preventsCriticalChanges(): bool
    {
        return in_array($this, [
            self::DisputedOwnership,
            self::Merged,
            self::DeletionPending,
            self::Archived,
        ], true);
    }
}
