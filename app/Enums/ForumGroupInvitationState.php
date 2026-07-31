<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumGroupInvitationState: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Revoked = 'revoked';
    case Expired = 'expired';

    public function label(): string
    {
        return __("forum_groups.invitation_states.{$this->value}");
    }
}
