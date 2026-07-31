<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\TaxonomyImportChunkResult;
use App\Enums\TaxonImportState;
use App\Models\TaxonImport;
use App\Services\TaxonomyImportProcessor;
use App\Services\TaxonomySnapshotReader;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

final readonly class ProcessTaxonomyImportChunk
{
    public function __construct(
        private TaxonomySnapshotReader $reader,
        private TaxonomyImportProcessor $processor,
    ) {}

    public function handle(
        TaxonImport $import,
        ?int $requestedChunkSize = null,
    ): TaxonomyImportChunkResult {
        $chunkSize = $requestedChunkSize ?? (int) config('taxonomy.chunk_size');

        if ($chunkSize < 1 || $chunkSize > 5_000) {
            throw new RuntimeException(__('messages.the_taxonomy_import_chunk_size_must_be_between_1_and_500_6bf0b658b4'));
        }

        return Cache::lock(
            'taxonomy-import:'.$import->id,
            (int) config('taxonomy.lock_seconds'),
        )->block(1, function () use ($chunkSize, $import): TaxonomyImportChunkResult {
            $import = TaxonImport::query()
                ->with('source')
                ->findOrFail($import->id);

            if (! $import->state->canProcess()) {
                throw new RuntimeException(__('messages.the_taxonomy_import_is_not_in_a_processable_state_b223ee972c'));
            }

            $import->forceFill([
                'state' => TaxonImportState::Importing,
                'started_at' => $import->started_at ?? now(),
                'error_report' => [],
            ])->save();

            try {
                $chunk = $this->reader->readChunk($import, $chunkSize);
                $result = $this->processor->process($import, $import->source, $chunk->rows);
                $import->forceFill([
                    'state' => $chunk->isComplete
                        ? TaxonImportState::Validating
                        : TaxonImportState::Importing,
                    'current_chunk' => $import->current_chunk + 1,
                    'processed_rows' => $import->processed_rows + $result['processed'],
                    'inserted_rows' => $import->inserted_rows + $result['inserted'],
                    'updated_rows' => $import->updated_rows + $result['updated'],
                    'unchanged_rows' => $import->unchanged_rows + $result['unchanged'],
                    'synonym_rows' => $import->synonym_rows + $result['synonyms'],
                    'error_rows' => $import->error_rows + $result['errors'],
                    'resume_token' => json_encode([
                        'offset' => $chunk->nextOffset,
                        'source_row' => $chunk->lastRow,
                    ], JSON_THROW_ON_ERROR),
                ])->save();

                return new TaxonomyImportChunkResult(
                    processed: $result['processed'],
                    inserted: $result['inserted'],
                    updated: $result['updated'],
                    unchanged: $result['unchanged'],
                    errors: $result['errors'],
                    isComplete: $chunk->isComplete,
                );
            } catch (Throwable $exception) {
                $import->forceFill([
                    'state' => TaxonImportState::Failed,
                    'error_report' => [
                        'code' => 'chunk-processing-failed',
                        'exception' => $exception::class,
                        'message' => $exception->getMessage(),
                        'chunk' => $import->current_chunk + 1,
                    ],
                ])->save();

                throw $exception;
            }
        });
    }
}
