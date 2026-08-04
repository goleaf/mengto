<?php

declare(strict_types=1);

namespace App\Enums;

enum PetFeatherType: string
{
    case Contour = 'contour';
    case Down = 'down';
    case Flight = 'flight';
    case Ornamental = 'ornamental';
    case Mixed = 'mixed';
    case Other = 'other';

    public function label(): string
    {
        return __("pet_profiles.body_covering.options.feather_type.{$this->value}");
    }
}
