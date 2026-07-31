<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentAudienceType: string
{
    case Everyone = 'everyone';
    case Registered = 'registered';
    case Followers = 'followers';
    case Friends = 'friends';
    case CloseCircle = 'close-circle';
    case Family = 'family';
    case Group = 'group';
    case EventParticipants = 'event-participants';
    case Selected = 'selected';
    case TemporaryLink = 'temporary-link';
    case AuthorOnly = 'author-only';

    public function label(): string
    {
        return __("content.audiences.{$this->value}");
    }
}
