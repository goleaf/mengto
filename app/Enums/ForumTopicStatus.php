<?php

namespace App\Enums;

enum ForumTopicStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case NeedsClarification = 'needs-clarification';
    case Answered = 'answered';
    case Resolved = 'resolved';
    case PartiallyResolved = 'partially-resolved';
    case Unanswered = 'unanswered';
    case Closed = 'closed';
    case Locked = 'locked';
    case Merged = 'merged';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Review => 'In review',
            self::Published => 'Open',
            self::NeedsClarification => 'Needs details',
            self::Answered => 'Answered',
            self::Resolved => 'Resolved',
            self::PartiallyResolved => 'Partly resolved',
            self::Unanswered => 'No answers yet',
            self::Closed => 'Closed',
            self::Locked => 'Locked',
            self::Merged => 'Merged',
            self::Archived => 'Archived',
        };
    }
}
