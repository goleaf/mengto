<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TaxonImport;
use App\Models\TaxonImportIssue;

/**
 * @extends ApplicationFactory<TaxonImportIssue>
 */
final class TaxonImportIssueFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'taxon_import_id' => TaxonImport::factory(),
            'source_row' => fake()->numberBetween(2, 1000),
            'source_record_id' => fake()->uuid(),
            'severity' => 'error',
            'code' => 'invalid-row',
            'context' => ['field' => 'scientificName'],
        ];
    }
}
