<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventStatus: string
{
    case Scheduled = 'scheduled';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return __('forum_events.statuses.'.$this->value);
    }

    public function acceptsRegistration(): bool
    {
        return $this === self::Scheduled;
    }
}
