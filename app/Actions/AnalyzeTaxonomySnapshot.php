<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TaxonImportState;
use App\Models\TaxonImport;
use App\Models\TaxonSource;
use App\Models\TaxonVersion;
use App\Models\User;
use App\Services\TaxonomySnapshotReader;
use Illuminate\Support\Facades\DB;

final readonly class AnalyzeTaxonomySnapshot
{
    public function __construct(
        private TaxonomySnapshotReader $reader,
    ) {}

    public function handle(
        TaxonSource $source,
        string $sourceVersion,
        string $disk,
        string $path,
        ?User $initiator = null,
    ): TaxonImport {
        $analysis = $this->reader->analyze($disk, $path);

        return DB::transaction(function () use (
            $analysis,
            $initiator,
            $source,
            $sourceVersion,
        ): TaxonImport {
            $existing = TaxonImport::query()
                ->where('taxon_source_id', $source->id)
                ->where('source_version', $sourceVersion)
                ->where('checksum', $analysis->checksum)
                ->first();

            if ($existing instanceof TaxonImport) {
                return $existing;
            }

            return TaxonImport::query()->create([
                'taxon_source_id' => $source->id,
                'initiated_by_user_id' => $initiator?->id,
                'source_version' => $sourceVersion,
                'state' => TaxonImportState::Ready,
                'checksum' => $analysis->checksum,
                'warning_rows' => $analysis->warningCount,
                'impact_report' => [
                    'source_rows' => $analysis->rowCount,
                    'preflight_warnings' => $analysis->warningCount,
                    'active_source_rows' => $source->active_taxon_import_id === null
                        ? 0
                        : TaxonVersion::query()
                            ->where('taxon_import_id', $source->active_taxon_import_id)
                            ->count(),
                ],
                'error_report' => [],
                'metadata' => [
                    'snapshot_disk' => $analysis->disk,
                    'snapshot_path' => $analysis->path,
                    'delimiter' => $analysis->delimiter,
                    'headers' => $analysis->headers,
                    'column_map' => $analysis->columnMap,
                    'source_rows' => $analysis->rowCount,
                ],
            ]);
        });
    }
}
