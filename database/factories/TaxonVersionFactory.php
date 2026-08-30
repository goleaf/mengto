<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Taxon;
use App\Models\TaxonImport;
use App\Models\TaxonVersion;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<TaxonVersion>
 */
final class TaxonVersionFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word()).' '.fake()->unique()->word();

        return [
            'taxon_id' => Taxon::factory(),
            'taxon_import_id' => TaxonImport::factory(),
            'taxon_source_id' => null,
            'source_record_id' => Str::uuid()->toString(),
            'rank' => 'species',
            'scientific_name' => $name,
            'canonical_name' => $name,
            'normalized_scientific_name' => Str::lower($name),
            'authorship' => 'Factory et al., 2026',
            'taxonomic_status' => 'accepted',
            'depth' => 1,
            'hierarchy_path' => null,
            'is_extinct' => false,
            'is_fossil' => false,
            'has_domestic_relevance' => false,
            'has_community_relevance' => true,
            'is_active_version' => false,
            'metadata' => [],
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (TaxonVersion $version): void {
            $import = TaxonImport::query()
                ->select(['id', 'taxon_source_id'])
                ->findOrFail($version->taxon_import_id);

            $version->taxon_source_id = $import->taxon_source_id;
        });
    }

    public function forImport(TaxonImport $import): static
    {
        return $this->state([
            'taxon_import_id' => $import->id,
            'taxon_source_id' => $import->taxon_source_id,
        ]);
    }
}
