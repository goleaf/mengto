<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Taxon;
use App\Models\TaxonChange;
use App\Models\TaxonImport;

/**
 * @extends ApplicationFactory<TaxonChange>
 */
final class TaxonChangeFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'taxon_id' => Taxon::factory(),
            'taxon_import_id' => TaxonImport::factory(),
            'change_type' => 'update',
            'before' => ['scientific_name' => 'Previous name'],
            'after' => ['scientific_name' => 'Current name'],
            'reason_code' => 'source-import',
            'metadata' => [],
        ];
    }
}
