<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TaxonImportState;
use App\Models\TaxonImport;
use App\Services\TaxonomyHierarchyValidator;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

final readonly class ValidateTaxonomyImport
{
    public function __construct(
        private TaxonomyHierarchyValidator $hierarchy,
    ) {}

    /**
     * @return array{orphans: int, unresolved: int, roots: int, resolved: int}
     */
    public function handle(TaxonImport $import): array
    {
        return Cache::lock(
            'taxonomy-import:'.$import->id,
            (int) config('taxonomy.lock_seconds'),
        )->block(1, function () use ($import): array {
            $import = TaxonImport::query()->findOrFail($import->id);

            if ($import->state !== TaxonImportState::Validating) {
                throw new RuntimeException(__('messages.the_taxonomy_import_is_not_ready_for_validation'));
            }

            $hierarchy = $this->hierarchy->rebuild($import);
            $hasErrors = $import->error_rows > 0
                || $hierarchy['orphans'] > 0
                || $hierarchy['unresolved'] > 0
                || $hierarchy['roots'] === 0;
            $impact = array_merge($import->impact_report ?? [], [
                'validation' => $hierarchy,
                'processed_rows' => $import->processed_rows,
                'inserted_rows' => $import->inserted_rows,
                'updated_rows' => $import->updated_rows,
                'unchanged_rows' => $import->unchanged_rows,
            ]);

            $import->forceFill([
                'state' => $hasErrors
                    ? TaxonImportState::Failed
                    : TaxonImportState::Completed,
                'impact_report' => $impact,
                'error_report' => $hasErrors
                    ? [
                        'code' => 'validation-failed',
                        'error_rows' => $import->error_rows,
                        'orphans' => $hierarchy['orphans'],
                        'unresolved' => $hierarchy['unresolved'],
                    ]
                    : [],
                'completed_at' => $hasErrors ? null : now(),
            ])->save();

            return $hierarchy;
        });
    }
}
