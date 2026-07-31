<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumGroupStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return __("forum_groups.statuses.{$this->value}");
    }
}
