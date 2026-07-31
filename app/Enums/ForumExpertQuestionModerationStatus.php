<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumExpertQuestionModerationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return __('forum_expert_sessions.moderation_statuses.'.$this->value);
    }
}
