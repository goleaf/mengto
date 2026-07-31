<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumPollStatus: string
{
    case Active = 'active';
    case Cancelled = 'cancelled';
    case Archived = 'archived';

    public function label(): string
    {
        return __("forum_polls.statuses.{$this->value}");
    }
}
