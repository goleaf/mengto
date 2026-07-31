<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialRelationshipStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Ended = 'ended';

    public function label(): string
    {
        return __("social_relationships.relationship_statuses.{$this->value}");
    }
}
