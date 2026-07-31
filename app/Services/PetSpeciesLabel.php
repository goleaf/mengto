<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

final class PetSpeciesLabel
{
    public function for(string $species): string
    {
        $key = 'pet_profiles.species.'.Str::slug($species);

        return Lang::has($key) ? __($key) : Str::headline($species);
    }
}
