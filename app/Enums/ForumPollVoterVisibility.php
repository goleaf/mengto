<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumPollVoterVisibility: string
{
    case Anonymous = 'anonymous';
    case Visible = 'visible';

    public function label(): string
    {
        return __("forum_polls.voter_visibility.{$this->value}");
    }
}
