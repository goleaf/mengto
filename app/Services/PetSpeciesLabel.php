<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetSpeciesConfidence;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

final class PetSpeciesLabel
{
    public function for(
        string $species,
        PetSpeciesConfidence|string|null $confidence = null,
    ): string {
        $resolved = PetSpeciesConfidence::normalize($species, $confidence);
        $displaySpecies = $resolved === PetSpeciesConfidence::Unidentified
            ? 'unknown'
            : $species;
        $key = 'pet_profiles.species.'.Str::slug($displaySpecies);
        $label = Lang::has($key) ? __($key) : Str::headline($displaySpecies);

        return $resolved === PetSpeciesConfidence::Possible
            ? __('pet_profiles.species_confidence.possible_label', ['species' => $label])
            : $label;
    }
}
