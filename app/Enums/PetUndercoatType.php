<?php

declare(strict_types=1);

namespace App\Enums;

enum PetUndercoatType: string
{
    case None = 'none';
    case Sparse = 'sparse';
    case Moderate = 'moderate';
    case Dense = 'dense';
    case Seasonal = 'seasonal';
    case Unknown = 'unknown';

    public function label(): string
    {
        return __("pet_profiles.body_covering.options.undercoat.{$this->value}");
    }
}
