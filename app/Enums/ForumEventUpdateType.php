<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventUpdateType: string
{
    case General = 'general';
    case Rescheduled = 'rescheduled';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('forum_events.update_types.'.$this->value);
    }
}
