<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumMentorProfileState: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return __("forum_mentorship.profile_states.{$this->value}");
    }
}
