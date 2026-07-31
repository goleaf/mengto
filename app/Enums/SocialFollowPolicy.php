<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialFollowPolicy: string
{
    case Public = 'public';
    case Approval = 'approval';
    case Nobody = 'nobody';

    public function label(): string
    {
        return __("social_relationships.follow_policies.{$this->value}");
    }
}
