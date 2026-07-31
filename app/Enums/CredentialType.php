<?php

declare(strict_types=1);

namespace App\Enums;

enum CredentialType: string
{
    case Identity = 'identity';
    case Education = 'education';
    case Qualification = 'qualification';
    case License = 'license';
    case Workplace = 'workplace';
    case Contact = 'contact';
    case OrganizationRole = 'organization-role';
    case OrganizationRegistration = 'organization-registration';
    case RescueOrganization = 'rescue-organization';
    case Shelter = 'shelter';
    case Breeder = 'breeder';
    case OrganizationRepresentative = 'organization-representative';

    public function label(): string
    {
        return __("credential_verification.type.{$this->value}");
    }

    public function verifiesIdentity(): bool
    {
        return $this === self::Identity;
    }

    public function verifiesOrganization(): bool
    {
        return in_array($this, [
            self::OrganizationRole,
            self::OrganizationRegistration,
            self::RescueOrganization,
            self::Shelter,
            self::Breeder,
            self::OrganizationRepresentative,
        ], true);
    }
}
