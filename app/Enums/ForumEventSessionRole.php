<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventSessionRole: string
{
    case Speaker = 'speaker';
    case Moderator = 'moderator';
    case Trainer = 'trainer';
    case Judge = 'judge';
    case RouteLeader = 'route_leader';
    case WelfareOfficer = 'welfare_officer';
    case Staff = 'staff';

    public function label(): string
    {
        return __('forum_events.session_roles.'.$this->value);
    }
}
