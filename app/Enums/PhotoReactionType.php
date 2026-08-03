<?php

declare(strict_types=1);

namespace App\Enums;

enum PhotoReactionType: string
{
    case Like = 'like';
    case Love = 'love';
    case Funny = 'funny';
    case Support = 'support';
    case Useful = 'useful';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
