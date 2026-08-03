<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventSessionStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Live = 'live';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Moved = 'moved';

    public function label(): string
    {
        return __('forum_events.session_statuses.'.$this->value);
    }

    public function blocksResources(): bool
    {
        return $this !== self::Cancelled;
    }
}
