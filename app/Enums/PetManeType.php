<?php

declare(strict_types=1);

namespace App\Enums;

enum PetManeType: string
{
    case None = 'none';
    case Short = 'short';
    case Long = 'long';
    case Thick = 'thick';
    case Sparse = 'sparse';
    case Trimmed = 'trimmed';
    case Other = 'other';

    public function label(): string
    {
        return __("pet_profiles.body_covering.options.mane_type.{$this->value}");
    }
}
