<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumMentorshipState: string
{
    case Requested = 'requested';
    case Active = 'active';
    case Declined = 'declined';
    case Completed = 'completed';
    case Ended = 'ended';
    case Cancelled = 'cancelled';

    public function isOpen(): bool
    {
        return in_array($this, [self::Requested, self::Active], true);
    }

    public function allowsMessages(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return __("forum_mentorship.states.{$this->value}");
    }
}
