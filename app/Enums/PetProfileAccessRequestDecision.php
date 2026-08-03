<?php

declare(strict_types=1);

namespace App\Enums;

enum PetProfileAccessRequestDecision: string
{
    case Approve = 'approve';
    case Reject = 'reject';
}
