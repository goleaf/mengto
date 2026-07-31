<?php

declare(strict_types=1);

namespace App\Enums;

enum AdoptionPlacementType: string
{
    case Adoption = 'adoption';
    case Foster = 'foster';

    public function label(): string
    {
        return __("adoption.placement_type.{$this->value}");
    }
}
