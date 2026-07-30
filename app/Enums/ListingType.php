<?php

namespace App\Enums;

enum ListingType: string
{
    case Sale = 'sale';
    case Service = 'service';
    case Rental = 'rental';
    case Adoption = 'adoption';
    case Exchange = 'exchange';
    case Free = 'free';
    case ShelterNeed = 'shelter-need';

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'For sale',
            self::Service => 'Pet service',
            self::Rental => 'For rent',
            self::Adoption => 'Adoption',
            self::Exchange => 'Exchange',
            self::Free => 'Free handover',
            self::ShelterNeed => 'Shelter need',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Sale => 'shopping-bag',
            self::Service => 'hand-heart',
            self::Rental => 'calendar-clock',
            self::Adoption => 'house-heart',
            self::Exchange => 'repeat-2',
            self::Free => 'gift',
            self::ShelterNeed => 'hand-heart',
        };
    }

    public function requestLabel(): string
    {
        return match ($this) {
            self::Sale => 'Request to buy',
            self::Service => 'Request service',
            self::Rental => 'Request rental',
            self::Adoption => 'Apply to adopt',
            self::Exchange => 'Propose exchange',
            self::Free => 'Request handover',
            self::ShelterNeed => 'Offer help',
        };
    }

    public function requestKind(): string
    {
        return match ($this) {
            self::Sale => 'purchase',
            self::Service => 'service',
            self::Rental => 'rental',
            self::Adoption => 'adoption',
            self::Exchange => 'exchange',
            self::Free => 'handover',
            self::ShelterNeed => 'donation',
        };
    }

    public function requiresManualReview(): bool
    {
        return in_array($this, [self::Adoption, self::ShelterNeed], true);
    }

    public function requiresPayment(): bool
    {
        return in_array($this, [self::Sale, self::Service, self::Rental], true);
    }
}
