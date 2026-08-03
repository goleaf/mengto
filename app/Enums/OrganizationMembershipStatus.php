<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationMembershipStatus: string
{
    case Invited = 'invited';
    case Active = 'active';
    case Removed = 'removed';
    case Expired = 'expired';

    public function label(): string
    {
        return __('organizations.membership_statuses.'.$this->value);
    }
}
