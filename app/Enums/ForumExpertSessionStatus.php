<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumExpertSessionStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('forum_expert_sessions.session_statuses.'.$this->value);
    }
}
