<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumPollType: string
{
    case SingleChoice = 'single-choice';
    case MultipleChoice = 'multiple-choice';
    case RankedChoice = 'ranked-choice';

    public function label(): string
    {
        return __("forum_polls.types.{$this->value}");
    }
}
