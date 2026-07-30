<?php

namespace App\Enums;

enum ListingType: string
{
    case Sale = 'sale';
    case Service = 'service';
    case Adoption = 'adoption';
    case Exchange = 'exchange';

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'For sale',
            self::Service => 'Pet service',
            self::Adoption => 'Adoption',
            self::Exchange => 'Exchange',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Sale => 'shopping-bag',
            self::Service => 'hand-heart',
            self::Adoption => 'house-heart',
            self::Exchange => 'repeat-2',
        };
    }
}
