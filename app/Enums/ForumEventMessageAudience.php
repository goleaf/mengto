<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventMessageAudience: string
{
    case Attendees = 'attendees';
    case Organizers = 'organizers';

    public function label(): string
    {
        return __('forum_events.message_audiences.'.$this->value);
    }
}
