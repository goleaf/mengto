<?php

declare(strict_types=1);

namespace App\Enums;

enum PetBreedOriginType: string
{
    case Single = 'single';
    case Mixed = 'mixed';
    case PossibleMultiple = 'possible-multiple';
    case NoBreed = 'no-breed';
    case Unknown = 'unknown';

    public function label(): string
    {
        return __("pet_profiles.breed_origin.types.{$this->value}");
    }

    public function acceptsEntries(): bool
    {
        return in_array($this, [self::Single, self::Mixed, self::PossibleMultiple], true);
    }
}
