<?php

declare(strict_types=1);

namespace App\Enums;

enum PetProfileAccessRequestType: string
{
    case CoOwnership = 'co-ownership';
    case Management = 'management';
    case TemporaryAccess = 'temporary-access';
    case RelationshipCorrection = 'relationship-correction';
    case OwnershipTransfer = 'ownership-transfer';

    public function label(): string
    {
        return __("pet_profiles.access_requests.types.{$this->value}");
    }

    public function defaultRole(): PetManagerRole
    {
        return match ($this) {
            self::CoOwnership => PetManagerRole::CoOwner,
            self::Management => PetManagerRole::ProfileAdministrator,
            self::TemporaryAccess => PetManagerRole::Caregiver,
            self::RelationshipCorrection => PetManagerRole::FamilyMember,
            self::OwnershipTransfer => PetManagerRole::PrimaryOwner,
        };
    }

    public function requiresProtectedApproval(): bool
    {
        return $this === self::OwnershipTransfer;
    }
}
