<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventVisibility: string
{
    case Public = 'public';
    case Members = 'members';
    case Group = 'group';
    case Private = 'private';

    public function label(): string
    {
        return __('forum_events.visibilities.'.$this->value);
    }
}
