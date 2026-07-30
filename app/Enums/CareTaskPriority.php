<?php

namespace App\Enums;

enum CareTaskPriority: string
{
    case Normal = 'normal';
    case Important = 'important';
    case Urgent = 'urgent';
    case Clinical = 'clinical';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::Important => 'Important',
            self::Urgent => 'Urgent',
            self::Clinical => 'Clinical instruction',
        };
    }
}
