<?php

declare(strict_types=1);

namespace App\Enums;

enum PetBirthDatePrecision: string
{
    case Exact = 'exact';
    case Estimated = 'estimated';
    case Month = 'month';
    case Year = 'year';
    case AgeEstimate = 'age-estimate';
    case Unknown = 'unknown';

    public function label(): string
    {
        return __("pet_profiles.birth_precision.{$this->value}");
    }

    public function usesDate(): bool
    {
        return in_array($this, [self::Exact, self::Estimated], true);
    }
}
