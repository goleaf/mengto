<?php

namespace App\Enums;

enum ModerationStatus: string
{
    case Approved = 'approved';
    case Pending = 'pending';
    case NeedsChanges = 'needs-changes';
    case Rejected = 'rejected';
    case Restricted = 'restricted';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }
}
