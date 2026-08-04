<?php

declare(strict_types=1);

namespace App\Enums;

enum PetSeasonalShedding: string
{
    case None = 'none';
    case Light = 'light';
    case Moderate = 'moderate';
    case Heavy = 'heavy';
    case SeasonalMolt = 'seasonal-molt';
    case Unknown = 'unknown';

    public function label(): string
    {
        return __("pet_profiles.body_covering.options.seasonal_shedding.{$this->value}");
    }
}
