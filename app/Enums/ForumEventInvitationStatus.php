<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventInvitationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Revoked = 'revoked';
    case Expired = 'expired';

    public function label(): string
    {
        return __('forum_events.invitation_statuses.'.$this->value);
    }
}
