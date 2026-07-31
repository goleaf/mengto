<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumReviewDecision: string
{
    case Support = 'support';
    case Oppose = 'oppose';
    case Abstain = 'abstain';
    case ChangesRequested = 'changes-requested';

    public function label(): string
    {
        return __("forum_review.decisions.{$this->value}");
    }
}
