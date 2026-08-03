<?php

declare(strict_types=1);

namespace App\Enums;

enum PetProfileMediaStatus: string
{
    case Active = 'active';
    case Superseded = 'superseded';
    case Removed = 'removed';
}
