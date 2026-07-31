<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Taxon;
use App\Models\TaxonExternalIdentifier;
use App\Models\TaxonSource;

/**
 * @extends ApplicationFactory<TaxonExternalIdentifier>
 */
final class TaxonExternalIdentifierFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'taxon_id' => Taxon::factory(),
            'taxon_source_id' => TaxonSource::factory(),
            'external_identifier' => fake()->unique()->uuid(),
            'identifier_type' => 'source-record',
            'version' => 'test-1',
            'is_active' => true,
        ];
    }
}
