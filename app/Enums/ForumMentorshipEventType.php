<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumMentorshipEventType: string
{
    case Requested = 'requested';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Ended = 'ended';
    case Completed = 'completed';
    case FeedbackSubmitted = 'feedback-submitted';
    case CompletionValidated = 'completion-validated';
    case Blocked = 'blocked';
    case Reported = 'reported';

    public function label(): string
    {
        return __("forum_mentorship.events.{$this->value}");
    }
}
