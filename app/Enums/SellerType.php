<?php

declare(strict_types=1);

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
        return (string) __("marketplace.seller_types.{$this->value}");
    }

    public function requiresVerification(): bool
    {
        return $this !== self::PrivateSeller;
    }
}
