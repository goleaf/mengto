<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumTopicStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case PendingModeration = 'pending-moderation';
    case NeedsClarification = 'needs-clarification';
    case Open = 'open';
    case Answered = 'answered';
    case PartiallySolved = 'partially-solved';
    case Solved = 'solved';
    case Disputed = 'disputed';
    case Outdated = 'outdated';
    case Locked = 'locked';
    case Archived = 'archived';
    case Merged = 'merged';
    case Redirected = 'redirected';
    case Removed = 'removed';
    case Restored = 'restored';

    /**
     * Compatibility values retained for existing rows. New mutations use the
     * canonical values above.
     */
    case Review = 'review';
    case Resolved = 'resolved';
    case PartiallyResolved = 'partially-resolved';
    case Unanswered = 'unanswered';
    case Closed = 'closed';

    public function label(): string
    {
        return __("forum_topic_lifecycle.states.{$this->value}");
    }

    public function canonical(): self
    {
        return match ($this) {
            self::Review => self::PendingModeration,
            self::Resolved => self::Solved,
            self::PartiallyResolved => self::PartiallySolved,
            self::Unanswered => self::Open,
            self::Closed => self::Locked,
            default => $this,
        };
    }

    public function isPubliclyVisible(): bool
    {
        return ! in_array($this->canonical(), [
            self::Draft,
            self::PendingModeration,
            self::NeedsClarification,
            self::Archived,
            self::Merged,
            self::Redirected,
            self::Removed,
        ], true);
    }

    public function acceptsAnswers(): bool
    {
        return in_array($this->canonical(), [
            self::Published,
            self::Open,
            self::Answered,
            self::PartiallySolved,
            self::Solved,
            self::Disputed,
            self::Outdated,
            self::Restored,
        ], true);
    }

    public function redirectsToAnotherTopic(): bool
    {
        return in_array($this->canonical(), [self::Merged, self::Redirected], true);
    }

    /** @return array<int, string> */
    public static function publicValues(): array
    {
        return array_values(array_map(
            static fn (self $status): string => $status->value,
            array_filter(
                self::cases(),
                static fn (self $status): bool => $status->isPubliclyVisible(),
            ),
        ));
    }
}
