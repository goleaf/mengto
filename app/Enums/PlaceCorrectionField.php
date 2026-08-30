<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceCorrectionField: string
{
    case Name = 'name';
    case Summary = 'summary';
    case PublicAddress = 'public_address';
    case PublicPhone = 'public_phone';
    case PublicWebsite = 'public_website';
    case PublicEmail = 'public_email';
    case PetRules = 'pet_rules';

    public function placeColumn(): string
    {
        return match ($this) {
            self::Name => 'name',
            self::Summary => 'summary',
            self::PublicAddress => 'public_address',
            self::PublicPhone => 'public_phone',
            self::PublicWebsite => 'public_website',
            self::PublicEmail => 'public_email',
            self::PetRules => 'pet_rules',
        };
    }

    /** @return list<string> */
    public function proposedValueRules(): array
    {
        return match ($this) {
            self::Name => ['required', 'string', 'min:2', 'max:180'],
            self::Summary => ['nullable', 'string', 'max:2000'],
            self::PublicAddress => ['nullable', 'string', 'max:500'],
            self::PublicPhone => ['nullable', 'string', 'max:80'],
            self::PublicWebsite => ['nullable', 'url', 'max:500'],
            self::PublicEmail => ['nullable', 'email', 'max:254'],
            self::PetRules => ['nullable', 'string', 'max:2000'],
        };
    }
}
