<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumGroupFileStatus: string
{
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return __("forum_polls.file_statuses.{$this->value}");
    }
}
