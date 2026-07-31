<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumJournalStatus: string
{
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return __("forum_journals.status.{$this->value}");
    }
}
