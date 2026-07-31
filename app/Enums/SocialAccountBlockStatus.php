<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialAccountBlockStatus: string
{
    case Active = 'active';
    case Revoked = 'revoked';
}
