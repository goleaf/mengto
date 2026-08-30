<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TaxonImportState;
use App\Models\TaxonImport;
use App\Models\TaxonSource;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<TaxonImport>
 */
final class TaxonImportFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $version = 'test-'.Str::lower(Str::random(8));

        return [
            'taxon_source_id' => TaxonSource::factory(),
            'source_version' => $version,
            'state' => TaxonImportState::Pending,
            'checksum' => hash('sha256', $version),
            'impact_report' => [],
            'error_report' => [
                'rows_received' => 12,
                'rows_rejected' => 1,
                'issues' => [['row' => 7, 'code' => 'unmatched-name']],
            ],
            'metadata' => ['source' => 'factory', 'version' => 1],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'state' => TaxonImportState::Completed,
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
        ]);
    }
}
