<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\ActivateTaxonomyImport;
use App\Actions\AnalyzeTaxonomySnapshot;
use App\Actions\ProcessTaxonomyImportChunk;
use App\Actions\ValidateTaxonomyImport;
use App\Enums\TaxonImportState;
use App\Models\TaxonSource;
use Illuminate\Console\Command;

final class ImportAnimalTaxonomy extends Command
{
    protected $signature = 'taxonomy:import
        {source : Taxon source stable key}
        {snapshot : Relative path inside the taxonomy snapshot directory}
        {--source-version= : Source release version}
        {--disk= : Configured private filesystem disk}
        {--chunk= : Rows per request, from 1 to 5000}
        {--activate : Activate the import after validation}';

    protected $description = 'Analyze and import a local versioned animal taxonomy snapshot';

    public function handle(
        AnalyzeTaxonomySnapshot $analyze,
        ProcessTaxonomyImportChunk $process,
        ValidateTaxonomyImport $validate,
        ActivateTaxonomyImport $activate,
    ): int {
        $source = TaxonSource::query()
            ->where('stable_key', (string) $this->argument('source'))
            ->first();

        if (! $source instanceof TaxonSource) {
            $this->components->error('The requested taxonomy source is not registered.');

            return self::FAILURE;
        }

        $sourceVersion = (string) ($this->option('source-version') ?: $source->version);
        $disk = (string) ($this->option('disk') ?: config('taxonomy.snapshot_disk'));
        $chunkSize = $this->option('chunk') !== null
            ? (int) $this->option('chunk')
            : (int) config('taxonomy.chunk_size');
        $import = $analyze->handle(
            source: $source,
            sourceVersion: $sourceVersion,
            disk: $disk,
            path: (string) $this->argument('snapshot'),
        );

        $this->components->info(sprintf(
            'Import %d is %s with checksum %s.',
            $import->id,
            $import->state->value,
            $import->checksum,
        ));

        while ($import->fresh()->state->canProcess()) {
            $result = $process->handle($import, $chunkSize);
            $import->refresh();
            $this->output->writeln(sprintf(
                'Chunk %d: %d processed, %d errors.',
                $import->current_chunk,
                $result->processed,
                $result->errors,
            ));

            if ($result->isComplete) {
                break;
            }
        }

        $import->refresh();

        if ($import->state === TaxonImportState::Validating) {
            $result = $validate->handle($import);
            $import->refresh();
            $this->output->writeln(sprintf(
                'Validation: %d resolved, %d orphaned, %d unresolved.',
                $result['resolved'],
                $result['orphans'],
                $result['unresolved'],
            ));
        }

        if ($import->state !== TaxonImportState::Completed) {
            $this->components->error('The taxonomy import did not complete validation.');

            return self::FAILURE;
        }

        if ((bool) $this->option('activate')) {
            $activate->handle($import);
            $this->components->info('The validated taxonomy import is now active.');
        } else {
            $this->components->info('The import is complete and remains inactive until explicitly activated.');
        }

        return self::SUCCESS;
    }
}
