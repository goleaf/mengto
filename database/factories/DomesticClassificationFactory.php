<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DomesticClassification;
use App\Models\Taxon;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<DomesticClassification>
 */
final class DomesticClassificationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'stable_key' => 'domestic.'.Str::slug($name),
            'taxon_id' => Taxon::factory(),
            'classification_type' => 'breed',
            'canonical_name' => $name,
            'is_active' => true,
            'aliases' => ['Companion animal classification'],
            'metadata' => ['source' => 'factory', 'version' => 1],
        ];
    }
}
