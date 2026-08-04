<?php

declare(strict_types=1);

namespace App\Enums;

enum PetSizeCategory: string
{
    case VerySmall = 'very-small';
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';
    case VeryLarge = 'very-large';
    case Individual = 'individual';
    case NotApplicable = 'not-applicable';

    public function label(): string
    {
        return __("pet_profiles.size.options.{$this->value}.label");
    }

    public function description(): string
    {
        return __("pet_profiles.size.options.{$this->value}.description");
    }
}
