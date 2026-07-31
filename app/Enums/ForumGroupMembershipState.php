<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumGroupMembershipState: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Rejected = 'rejected';
    case Removed = 'removed';
    case Banned = 'banned';
    case Left = 'left';

    public function label(): string
    {
        return __("forum_groups.membership_states.{$this->value}");
    }
}
