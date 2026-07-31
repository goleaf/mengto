<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventRegistrationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Waitlisted = 'waitlisted';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case CheckedIn = 'checked_in';

    public function label(): string
    {
        return __('forum_events.registration_statuses.'.$this->value);
    }

    public function consumesSeat(): bool
    {
        return in_array($this, [
            self::Confirmed,
            self::CheckedIn,
        ], true);
    }

    public function canCancel(): bool
    {
        return in_array($this, [
            self::Pending,
            self::Confirmed,
            self::Waitlisted,
        ], true);
    }
}
