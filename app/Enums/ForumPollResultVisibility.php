<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumPollResultVisibility: string
{
    case Public = 'public';
    case AfterVote = 'after-vote';
    case AfterClose = 'after-close';

    public function label(): string
    {
        return __("forum_polls.result_visibility.{$this->value}");
    }
}
