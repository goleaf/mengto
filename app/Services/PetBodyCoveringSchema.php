<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class PetBodyCoveringSchema
{
    /**
     * @return array{
     *     coat: bool,
     *     feathers: bool,
     *     scales: bool,
     *     skin: bool,
     *     mane: bool,
     *     shedding: bool
     * }
     */
    public function for(string $species): array
    {
        $normalizedSpecies = Str::lower(trim($species));
        $coat = in_array($normalizedSpecies, [
            'dog',
            'cat',
            'rabbit',
            'rodent',
            'horse',
            'farm-animal',
            'exotic',
            'other',
        ], true);

        return [
            'coat' => $coat,
            'feathers' => in_array($normalizedSpecies, ['bird', 'exotic', 'other'], true),
            'scales' => in_array($normalizedSpecies, ['fish', 'reptile', 'exotic', 'other'], true),
            'skin' => true,
            'mane' => in_array($normalizedSpecies, ['horse', 'farm-animal', 'exotic', 'other'], true),
            'shedding' => $normalizedSpecies !== 'unknown',
        ];
    }
}
