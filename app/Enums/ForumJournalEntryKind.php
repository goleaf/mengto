<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumJournalEntryKind: string
{
    case Entry = 'entry';
    case Milestone = 'milestone';
    case Setback = 'setback';

    public function label(): string
    {
        return __("forum_journals.entry_kinds.{$this->value}");
    }
}
