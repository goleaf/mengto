<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventRegistrationPolicy: string
{
    case Open = 'open';
    case Approval = 'approval';
    case Invitation = 'invitation';

    public function label(): string
    {
        return __('forum_events.registration_policies.'.$this->value);
    }
}
