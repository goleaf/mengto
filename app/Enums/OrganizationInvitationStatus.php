<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationInvitationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Revoked = 'revoked';
    case Expired = 'expired';

    public function label(): string
    {
        return __('organizations.invitation_statuses.'.$this->value);
    }
}
