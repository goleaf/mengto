<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumReviewPanelState: string
{
    case AwaitingAssignment = 'awaiting-assignment';
    case InReview = 'in-review';
    case QuorumReached = 'quorum-reached';
    case Decided = 'decided';
    case Overridden = 'overridden';
    case Appealed = 'appealed';
    case Closed = 'closed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function isOpen(): bool
    {
        return in_array($this, [
            self::AwaitingAssignment,
            self::InReview,
            self::QuorumReached,
            self::Appealed,
        ], true);
    }

    public function label(): string
    {
        return __("forum_review.panel_states.{$this->value}");
    }
}
