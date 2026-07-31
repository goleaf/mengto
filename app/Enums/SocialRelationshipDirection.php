<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialRelationshipDirection: string
{
    case Directed = 'directed';
    case Symmetric = 'symmetric';
}
