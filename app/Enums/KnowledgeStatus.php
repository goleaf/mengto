<?php

namespace App\Enums;

enum KnowledgeStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Outdated = 'outdated';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Review => 'Editorial review',
            self::Published => 'Reviewed',
            self::Outdated => 'May be outdated',
            self::Archived => 'Archived',
        };
    }
}
