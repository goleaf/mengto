<?php

declare(strict_types=1);

namespace App\Enums;

enum PetManagerRole: string
{
    case PrimaryOwner = 'primary-owner';
    case CoOwner = 'co-owner';
    case LegalRepresentative = 'legal-representative';
    case FamilyMember = 'family-member';
    case Shelter = 'shelter';
    case FosterCarer = 'foster-carer';
    case Sitter = 'sitter';
    case Caregiver = 'caregiver';
    case ProfileAdministrator = 'profile-administrator';
    case Specialist = 'specialist';
    case Finder = 'finder';
    case PreviousOwner = 'previous-owner';
    case OrganizationAdministrator = 'organization-administrator';
    case Volunteer = 'volunteer';
    case Other = 'other';

    public function label(): string
    {
        return __("pet_profiles.manager_roles.{$this->value}");
    }

    /** @return list<PetProfilePermission> */
    public function defaultPermissions(): array
    {
        $basic = [
            PetProfilePermission::View,
            PetProfilePermission::EditBasics,
            PetProfilePermission::ManageMedia,
        ];
        $social = [PetProfilePermission::ManageSocial];
        $care = [
            PetProfilePermission::ManageCare,
            PetProfilePermission::ActivateLost,
        ];

        return match ($this) {
            self::PrimaryOwner,
            self::LegalRepresentative,
            self::OrganizationAdministrator => PetProfilePermission::cases(),
            self::CoOwner => [
                ...$basic,
                ...$social,
                ...$care,
                PetProfilePermission::ManagePrivacy,
                PetProfilePermission::Publish,
                PetProfilePermission::ViewMedical,
                PetProfilePermission::ManageDocuments,
                PetProfilePermission::ManageDevices,
                PetProfilePermission::InviteCaregiver,
                PetProfilePermission::ManageManagers,
                PetProfilePermission::ViewExactLocation,
                PetProfilePermission::PublishAdoption,
            ],
            self::Shelter => [
                ...$basic,
                ...$social,
                ...$care,
                PetProfilePermission::ManagePrivacy,
                PetProfilePermission::Publish,
                PetProfilePermission::ViewMedical,
                PetProfilePermission::ManageDocuments,
                PetProfilePermission::ManageManagers,
                PetProfilePermission::PublishAdoption,
                PetProfilePermission::TransferOwnership,
            ],
            self::FosterCarer => [
                ...$basic,
                ...$social,
                ...$care,
                PetProfilePermission::Publish,
                PetProfilePermission::ViewMedical,
            ],
            self::FamilyMember => [...$basic, ...$care, ...$social, PetProfilePermission::Publish],
            self::Sitter,
            self::Caregiver => [
                PetProfilePermission::View,
                PetProfilePermission::ManageCare,
                PetProfilePermission::ViewExactLocation,
            ],
            self::ProfileAdministrator => [
                ...$basic,
                ...$social,
                PetProfilePermission::Publish,
            ],
            self::Specialist => [
                PetProfilePermission::View,
                PetProfilePermission::ViewMedical,
            ],
            self::Finder => [
                PetProfilePermission::View,
                PetProfilePermission::EditBasics,
                PetProfilePermission::ManageMedia,
                PetProfilePermission::ActivateLost,
            ],
            self::Volunteer => [
                PetProfilePermission::View,
                PetProfilePermission::ManageCare,
                PetProfilePermission::ManageMedia,
            ],
            self::PreviousOwner,
            self::Other => [PetProfilePermission::View],
        };
    }
}
