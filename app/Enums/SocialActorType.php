<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialActorType: string
{
    case User = 'user';
    case Pet = 'pet';
    case Expert = 'expert';
    case Group = 'group';

    public function label(): string
    {
        return __("social_relationships.actor_types.{$this->value}");
    }
}
