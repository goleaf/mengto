<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventType: string
{
    case Walk = 'walk';
    case Training = 'training';
    case Show = 'show';
    case Adoption = 'adoption';
    case Volunteer = 'volunteer';
    case Celebration = 'celebration';
    case OnlineSession = 'online_session';
    case ClubMeetup = 'club_meetup';
    case Other = 'other';

    public function label(): string
    {
        return __('forum_events.types.'.$this->value);
    }
}
