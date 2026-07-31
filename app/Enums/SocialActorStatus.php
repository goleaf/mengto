<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialActorStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Detached = 'detached';
}
