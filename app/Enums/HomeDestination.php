<?php

declare(strict_types=1);

namespace App\Enums;

enum HomeDestination
{
    case Join;
    case VerifyEmail;
    case ContentFeed;
    case UnavailableAccount;
}
