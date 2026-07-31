<?php

declare(strict_types=1);

namespace App\Enums;

enum PetManagerStatus: string
{
    case Invited = 'invited';
    case Active = 'active';
    case Suspended = 'suspended';
    case Revoked = 'revoked';
    case Expired = 'expired';
    case Declined = 'declined';

    public function label(): string
    {
        return __("pet_profiles.manager_statuses.{$this->value}");
    }
}
