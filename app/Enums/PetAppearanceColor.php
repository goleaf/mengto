<?php

declare(strict_types=1);

namespace App\Enums;

enum PetAppearanceColor: string
{
    case Beige = 'beige';
    case Black = 'black';
    case Blue = 'blue';
    case Brown = 'brown';
    case Cream = 'cream';
    case Gold = 'gold';
    case Gray = 'gray';
    case Green = 'green';
    case Orange = 'orange';
    case Pink = 'pink';
    case Purple = 'purple';
    case Red = 'red';
    case Silver = 'silver';
    case Tan = 'tan';
    case Transparent = 'transparent';
    case White = 'white';
    case Yellow = 'yellow';
    case Other = 'other';

    public function label(): string
    {
        return __("pet_profiles.appearance.colors.{$this->value}");
    }
}
