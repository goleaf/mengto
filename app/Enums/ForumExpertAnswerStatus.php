<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumExpertAnswerStatus: string
{
    case Published = 'published';
    case Corrected = 'corrected';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return __('forum_expert_sessions.answer_statuses.'.$this->value);
    }
}
