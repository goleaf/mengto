<?php

namespace App\Enums;

enum SellerType: string
{
    case PrivateSeller = 'private';
    case Business = 'business';
    case Specialist = 'specialist';
    case Shelter = 'shelter';
    case OfficialBrand = 'official-brand';

    public function label(): string
    {
        return match ($this) {
            self::PrivateSeller => 'Private seller',
            self::Business => 'Business seller',
            self::Specialist => 'Verified specialist',
            self::Shelter => 'Shelter or charity',
            self::OfficialBrand => 'Official brand',
        };
    }

    public function requiresVerification(): bool
    {
        return $this !== self::PrivateSeller;
    }
}
