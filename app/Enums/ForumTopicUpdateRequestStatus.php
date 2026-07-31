<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumTopicUpdateRequestStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
    case Superseded = 'superseded';

    public function isFinal(): bool
    {
        return $this !== self::Pending;
    }

    public function label(): string
    {
        return __("forum_topic_lifecycle.request_statuses.{$this->value}");
    }
}
