<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumExpertQuestionStatus: string
{
    case Queued = 'queued';
    case Selected = 'selected';
    case Answered = 'answered';
    case Declined = 'declined';
    case Withdrawn = 'withdrawn';
    case Removed = 'removed';

    public function label(): string
    {
        return __('forum_expert_sessions.question_statuses.'.$this->value);
    }

    public function isUnanswered(): bool
    {
        return in_array($this, [self::Queued, self::Selected, self::Declined], true);
    }
}
