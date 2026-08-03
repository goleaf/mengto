<?php

declare(strict_types=1);

namespace App\Enums;

enum PetSpeciesConfidence: string
{
    case Confirmed = 'confirmed';
    case Possible = 'possible';
    case Unidentified = 'unidentified';

    public function label(): string
    {
        return __("pet_profiles.species_confidence.{$this->value}");
    }

    public static function normalize(string $species, self|string|null $confidence): self
    {
        if ($species === 'unknown') {
            return self::Unidentified;
        }

        $resolved = $confidence instanceof self
            ? $confidence
            : self::tryFrom((string) $confidence);

        if ($resolved === self::Possible && in_array($species, ['cat', 'dog'], true)) {
            return self::Possible;
        }

        return self::Confirmed;
    }

    /** @return list<self> */
    public static function optionsFor(string $species): array
    {
        return match ($species) {
            'unknown' => [self::Unidentified],
            'cat', 'dog' => [self::Confirmed, self::Possible],
            default => [self::Confirmed],
        };
    }
}
