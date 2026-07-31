<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumPollEligibility: string
{
    case GroupMembers = 'group-members';
    case TrustedMembers = 'trusted-members';
    case LocationMembers = 'location-members';

    public function label(): string
    {
        return __("forum_polls.eligibility.{$this->value}");
    }
}
