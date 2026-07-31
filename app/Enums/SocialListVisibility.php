<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialListVisibility: string
{
    case Everyone = 'everyone';
    case Friends = 'friends';
    case Mutuals = 'mutuals';
    case CountOnly = 'count-only';
    case Hidden = 'hidden';
    case Managers = 'managers';

    public function label(): string
    {
        return __("social_relationships.list_visibility.{$this->value}");
    }
}
