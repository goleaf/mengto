<?php

namespace App\Enums;

enum ExpertProfileStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Paused = 'paused';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Pending => 'Verification pending',
            self::Published => 'Published',
            self::Paused => 'Temporarily unavailable',
            self::Suspended => 'Restricted',
        };
    }
}
