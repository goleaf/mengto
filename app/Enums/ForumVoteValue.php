<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumVoteValue: string
{
    case Helpful = 'helpful';
    case NotHelpful = 'not-helpful';
    case NeedsSource = 'needs-source';
    case Outdated = 'outdated';
    case Dangerous = 'dangerous';
    case OffTopic = 'off-topic';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
