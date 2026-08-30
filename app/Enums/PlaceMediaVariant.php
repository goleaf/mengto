<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceMediaVariant: string
{
    case Fallback = 'fallback';
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';
}
