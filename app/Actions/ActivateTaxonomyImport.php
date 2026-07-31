<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TaxonImportState;
use App\Models\Taxon;
use App\Models\TaxonExternalIdentifier;
use App\Models\TaxonImport;
use App\Models\TaxonName;
use App\Models\TaxonSource;
use App\Models\TaxonVersion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ActivateTaxonomyImport
{
    public function handle(TaxonImport $import): void
    {
        Cache::lock(
            'taxonomy-source-activation:'.$import->taxon_source_id,
            (int) config('taxonomy.lock_seconds'),
        )->block(1, function () use ($import): void {
            DB::transaction(function () use ($import): void {
                $import = TaxonImport::query()
                    ->lockForUpdate()
                    ->findOrFail($import->id);
                $source = TaxonSource::query()
                    ->lockForUpdate()
                    ->findOrFail($import->taxon_source_id);

                if (! $import->state->canActivate()) {
                    throw new RuntimeException(__('messages.only_a_completed_taxonomy_import_can_be_activated_2561491aba'));
                }

                $previousImportId = $source->active_taxon_import_id;

                TaxonVersion::query()
                    ->where('taxon_source_id', $source->id)
                    ->where('is_active_version', true)
                    ->update(['is_active_version' => false]);
                TaxonVersion::query()
                    ->where('taxon_import_id', $import->id)
                    ->update(['is_active_version' => true]);
                TaxonName::query()
                    ->where('taxon_source_id', $source->id)
                    ->where('is_local_override', false)
                    ->update(['is_active' => false]);
                TaxonName::query()
                    ->where('taxon_import_id', $import->id)
                    ->update(['is_active' => true]);
                TaxonExternalIdentifier::query()
                    ->where('taxon_source_id', $source->id)
                    ->update(['is_active' => false]);
                TaxonExternalIdentifier::query()
                    ->where('taxon_source_id', $source->id)
                    ->whereIn(
                        'taxon_id',
                        TaxonVersion::query()
                            ->where('taxon_import_id', $import->id)
                            ->select('taxon_id'),
                    )
                    ->update(['is_active' => true]);

                $sourceTaxa = TaxonExternalIdentifier::query()
                    ->where('taxon_source_id', $source->id)
                    ->select('taxon_id');
                $currentTaxa = TaxonVersion::query()
                    ->where('taxon_import_id', $import->id)
                    ->select('taxon_id');
                Taxon::query()
                    ->whereIn('id', $sourceTaxa)
                    ->whereNotIn('id', $currentTaxa)
                    ->update([
                        'is_active' => false,
                        'archived_at' => now(),
                    ]);
                Taxon::query()
                    ->whereIn('id', $currentTaxa)
                    ->update([
                        'is_active' => true,
                        'archived_at' => null,
                    ]);

                if ($previousImportId !== null && $previousImportId !== $import->id) {
                    TaxonImport::query()
                        ->whereKey($previousImportId)
                        ->where('state', TaxonImportState::Active->value)
                        ->update(['state' => TaxonImportState::Completed->value]);
                }

                $import->forceFill([
                    'state' => TaxonImportState::Active,
                    'activated_at' => now(),
                ])->save();
                $source->forceFill([
                    'version' => $import->source_version,
                    'checksum' => $import->checksum,
                    'active_taxon_import_id' => $import->id,
                ])->save();
            });

            Cache::forget('taxonomy:high-level-tree');
            Cache::forget('taxonomy:popular-groups');
        });
    }
}
