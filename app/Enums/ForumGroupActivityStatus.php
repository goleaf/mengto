<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumGroupActivityStatus: string
{
    case Scheduled = 'scheduled';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return __("forum_polls.activity_statuses.{$this->value}");
    }
}
