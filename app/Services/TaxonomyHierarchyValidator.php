<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TaxonImport;
use App\Models\TaxonVersion;
use Carbon\CarbonImmutable;

final class TaxonomyHierarchyValidator
{
    /**
     * @return array{orphans: int, unresolved: int, roots: int, resolved: int}
     */
    public function rebuild(TaxonImport $import): array
    {
        TaxonVersion::query()
            ->where('taxon_import_id', $import->id)
            ->update([
                'depth' => 0,
                'hierarchy_path' => null,
            ]);

        $resolved = 0;
        $roots = 0;
        $chunkSize = (int) config('taxonomy.chunk_size');

        while (true) {
            $rootVersions = TaxonVersion::query()
                ->where('taxon_import_id', $import->id)
                ->whereNull('parent_taxon_id')
                ->whereNull('hierarchy_path')
                ->with('taxon:id,stable_key')
                ->orderBy('id')
                ->limit($chunkSize)
                ->get();

            if ($rootVersions->isEmpty()) {
                break;
            }

            $now = CarbonImmutable::now();
            $rows = $rootVersions->map(static fn (TaxonVersion $version): array => [
                'id' => $version->id,
                'taxon_id' => $version->taxon_id,
                'taxon_import_id' => $version->taxon_import_id,
                'taxon_source_id' => $version->taxon_source_id,
                'source_record_id' => $version->source_record_id,
                'rank' => $version->rank,
                'scientific_name' => $version->scientific_name,
                'canonical_name' => $version->canonical_name,
                'normalized_scientific_name' => $version->normalized_scientific_name,
                'taxonomic_status' => $version->taxonomic_status,
                'depth' => 0,
                'hierarchy_path' => '/'.$version->taxon->stable_key,
                'is_extinct' => $version->is_extinct,
                'is_fossil' => $version->is_fossil,
                'has_domestic_relevance' => $version->has_domestic_relevance,
                'has_community_relevance' => $version->has_community_relevance,
                'is_active_version' => false,
                'created_at' => $version->created_at,
                'updated_at' => $now,
            ])->all();

            TaxonVersion::query()->upsert(
                $rows,
                ['id'],
                ['depth', 'hierarchy_path', 'updated_at'],
            );
            $count = count($rows);
            $roots += $count;
            $resolved += $count;
        }

        while (true) {
            $resolvedParentTaxa = TaxonVersion::query()
                ->where('taxon_import_id', $import->id)
                ->whereNotNull('hierarchy_path')
                ->select('taxon_id');
            $children = TaxonVersion::query()
                ->where('taxon_import_id', $import->id)
                ->whereNull('hierarchy_path')
                ->whereIn('parent_taxon_id', $resolvedParentTaxa)
                ->with('taxon:id,stable_key')
                ->orderBy('id')
                ->limit($chunkSize)
                ->get();

            if ($children->isEmpty()) {
                break;
            }

            $parentVersions = TaxonVersion::query()
                ->where('taxon_import_id', $import->id)
                ->whereIn('taxon_id', $children->pluck('parent_taxon_id'))
                ->get(['taxon_id', 'depth', 'hierarchy_path'])
                ->keyBy('taxon_id');
            $now = CarbonImmutable::now();
            $rows = [];

            foreach ($children as $child) {
                $parent = $parentVersions->get($child->parent_taxon_id);

                if ($parent === null || $parent->hierarchy_path === null) {
                    continue;
                }

                $rows[] = [
                    'id' => $child->id,
                    'taxon_id' => $child->taxon_id,
                    'taxon_import_id' => $child->taxon_import_id,
                    'taxon_source_id' => $child->taxon_source_id,
                    'source_record_id' => $child->source_record_id,
                    'rank' => $child->rank,
                    'scientific_name' => $child->scientific_name,
                    'canonical_name' => $child->canonical_name,
                    'normalized_scientific_name' => $child->normalized_scientific_name,
                    'taxonomic_status' => $child->taxonomic_status,
                    'depth' => $parent->depth + 1,
                    'hierarchy_path' => $parent->hierarchy_path.'/'.$child->taxon->stable_key,
                    'is_extinct' => $child->is_extinct,
                    'is_fossil' => $child->is_fossil,
                    'has_domestic_relevance' => $child->has_domestic_relevance,
                    'has_community_relevance' => $child->has_community_relevance,
                    'is_active_version' => false,
                    'created_at' => $child->created_at,
                    'updated_at' => $now,
                ];
            }

            if ($rows === []) {
                break;
            }

            TaxonVersion::query()->upsert(
                $rows,
                ['id'],
                ['depth', 'hierarchy_path', 'updated_at'],
            );
            $resolved += count($rows);
        }

        $parentTaxa = TaxonVersion::query()
            ->where('taxon_import_id', $import->id)
            ->select('taxon_id');
        $orphans = TaxonVersion::query()
            ->where('taxon_import_id', $import->id)
            ->whereNotNull('parent_taxon_id')
            ->whereNotIn('parent_taxon_id', $parentTaxa)
            ->count();
        $unresolved = TaxonVersion::query()
            ->where('taxon_import_id', $import->id)
            ->whereNull('hierarchy_path')
            ->count();

        return [
            'orphans' => $orphans,
            'unresolved' => $unresolved,
            'roots' => $roots,
            'resolved' => $resolved,
        ];
    }
}
