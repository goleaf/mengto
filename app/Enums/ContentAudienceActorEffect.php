<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentAudienceActorEffect: string
{
    case Include = 'include';
    case Exclude = 'exclude';
}
