<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventReviewStatus: string
{
    case Published = 'published';
    case Hidden = 'hidden';

    public function label(): string
    {
        return __('forum_events.review_statuses.'.$this->value);
    }
}
