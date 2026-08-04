<?php

declare(strict_types=1);

namespace App\Enums;

enum PetAppearancePattern: string
{
    case Spots = 'spots';
    case Stripes = 'stripes';
    case Gradient = 'gradient';

    public function label(): string
    {
        return __("pet_profiles.appearance.patterns.{$this->value}");
    }
}
