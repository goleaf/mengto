<?php

declare(strict_types=1);

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
        return (string) __("marketplace.listing_types.{$this->value}");
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
        return (string) __("marketplace.request_labels.{$this->value}");
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
