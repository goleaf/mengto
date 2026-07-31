<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Taxon;
use App\Models\TaxonName;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<TaxonName>
 */
final class TaxonNameFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'taxon_id' => Taxon::factory(),
            'locale' => 'en',
            'language' => 'English',
            'script' => 'Latn',
            'name' => $name,
            'normalized_name' => Str::lower($name),
            'name_type' => 'common',
            'is_preferred' => true,
            'is_verified' => true,
            'is_local_override' => false,
            'is_active' => true,
            'metadata' => [],
        ];
    }
}
