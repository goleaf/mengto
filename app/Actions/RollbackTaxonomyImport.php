<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TaxonImportState;
use App\Models\TaxonImport;
use App\Models\TaxonSource;
use RuntimeException;

final readonly class RollbackTaxonomyImport
{
    public function __construct(
        private ActivateTaxonomyImport $activate,
    ) {}

    public function handle(TaxonSource $source, TaxonImport $target): void
    {
        if (
            $target->taxon_source_id !== $source->id
            || ! $target->state->canActivate()
        ) {
            throw new RuntimeException(__('messages.the_requested_taxonomy_rollback_target_is_not_eligible_08a54eff4b'));
        }

        $currentImportId = $source->active_taxon_import_id;
        $this->activate->handle($target);

        if ($currentImportId !== null && $currentImportId !== $target->id) {
            TaxonImport::query()
                ->whereKey($currentImportId)
                ->where('state', TaxonImportState::Completed->value)
                ->update(['state' => TaxonImportState::RolledBack->value]);
        }
    }
}
