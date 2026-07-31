<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumTopicLifecycleEventType: string
{
    case StateChanged = 'state-changed';
    case UpdateRequested = 'update-requested';
    case UpdateProposed = 'update-proposed';
    case UpdateReviewed = 'update-reviewed';
    case AuthorUpdated = 'author-updated';
    case Bumped = 'bumped';
    case LegalHoldApplied = 'legal-hold-applied';
    case LegalHoldReleased = 'legal-hold-released';
    case Redirected = 'redirected';
    case Merged = 'merged';

    public function label(): string
    {
        return __("forum_topic_lifecycle.events.{$this->value}");
    }
}
