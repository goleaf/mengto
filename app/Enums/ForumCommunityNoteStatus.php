<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumCommunityNoteStatus: string
{
    case Proposed = 'proposed';
    case GatheringEvidence = 'gathering-evidence';
    case InReview = 'in-review';
    case AwaitingAuthorResponse = 'awaiting-author-response';
    case CommunityAssessed = 'community-assessed';
    case ModeratorReview = 'moderator-review';
    case Published = 'published';
    case Revised = 'revised';
    case Rejected = 'rejected';
    case Archived = 'archived';
    case RevalidationDue = 'revalidation-due';

    public function isPublic(): bool
    {
        return in_array($this, [
            self::Published,
            self::Revised,
            self::RevalidationDue,
        ], true);
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [
            self::Rejected,
            self::Archived,
        ], true);
    }

    public function label(): string
    {
        return __("forum_review.note_states.{$this->value}");
    }
}
