<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TaxonImportState;
use App\Models\TaxonImport;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

final class CancelTaxonomyImport
{
    public function handle(TaxonImport $import): void
    {
        Cache::lock(
            'taxonomy-import:'.$import->id,
            (int) config('taxonomy.lock_seconds'),
        )->block(1, function () use ($import): void {
            $import = TaxonImport::query()->findOrFail($import->id);

            if (in_array($import->state, [
                TaxonImportState::Completed,
                TaxonImportState::Active,
                TaxonImportState::RolledBack,
            ], true)) {
                throw new RuntimeException(__('messages.a_completed_or_active_taxonomy_import_cannot_be_cancelled'));
            }

            $import->forceFill([
                'state' => TaxonImportState::Cancelled,
                'cancelled_at' => now(),
            ])->save();
        });
    }
}
