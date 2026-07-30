<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Reserved = 'reserved';
    case Completed = 'completed';
    case Removed = 'removed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Available',
            self::Reserved => 'Reserved',
            self::Completed => 'Completed',
            self::Removed => 'Removed',
        };
    }
}
