<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventVisibility: string
{
    case Public = 'public';
    case Unlisted = 'unlisted';
    case Members = 'members';
    case Organization = 'organization';
    case Group = 'group';
    case Invitation = 'invitation';
    case Private = 'private';

    public function label(): string
    {
        return __('forum_events.visibilities.'.$this->value);
    }
}
