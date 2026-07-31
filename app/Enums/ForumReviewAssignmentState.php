<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumReviewAssignmentState: string
{
    case Assigned = 'assigned';
    case Submitted = 'submitted';
    case Recused = 'recused';
    case Replaced = 'replaced';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __("forum_review.assignment_states.{$this->value}");
    }
}
