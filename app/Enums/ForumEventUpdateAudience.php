<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventUpdateAudience: string
{
    case Public = 'public';
    case Attendees = 'attendees';

    public function label(): string
    {
        return __('forum_events.update_audiences.'.$this->value);
    }
}
