<?php

declare(strict_types=1);

use App\Enums\TaxonImportState;
use App\Models\CommunityAnimalGroup;
use App\Models\Taxon;
use App\Models\TaxonImport;
use App\Models\TaxonName;
use App\Models\TaxonSource;
use App\Models\TaxonVersion;
use Database\Seeders\ForumSystemSeeder;

test('core animal taxonomy is idempotent versioned and searchable', function () {
    $this->seed(ForumSystemSeeder::class);

    $first = [
        'taxa' => Taxon::query()->count(),
        'versions' => TaxonVersion::query()->count(),
        'names' => TaxonName::query()->count(),
        'groups' => CommunityAnimalGroup::query()->count(),
    ];
    $animaliaId = Taxon::query()->where('stable_key', 'taxon.core.animalia')->value('id');

    $this->seed(ForumSystemSeeder::class);

    expect([
        'taxa' => Taxon::query()->count(),
        'versions' => TaxonVersion::query()->count(),
        'names' => TaxonName::query()->count(),
        'groups' => CommunityAnimalGroup::query()->count(),
    ])->toBe($first)
        ->and(Taxon::query()->where('stable_key', 'taxon.core.animalia')->value('id'))
        ->toBe($animaliaId)
        ->and(TaxonVersion::query()
            ->active()
            ->where('normalized_scientific_name', 'canis lupus familiaris')
            ->exists())->toBeTrue()
        ->and(TaxonName::query()
            ->search('domestic dog')
            ->where('name_type', 'preferred common')
            ->exists())->toBeTrue()
        ->and(CommunityAnimalGroup::query()->count())->toBe(30);
});

test('core taxonomy has one active import and an acyclic hierarchy', function () {
    $this->seed(ForumSystemSeeder::class);

    $source = TaxonSource::query()
        ->where('stable_key', 'platform-core-animal-taxonomy')
        ->with('activeImport')
        ->firstOrFail();

    expect($source->checksum)->not->toBeNull()
        ->and($source->activeImport)->not->toBeNull()
        ->and($source->activeImport?->state)->toBe(TaxonImportState::Active)
        ->and(TaxonImport::query()
            ->where('taxon_source_id', $source->id)
            ->where('state', TaxonImportState::Active->value)
            ->count())->toBe(1);

    $versions = TaxonVersion::query()
        ->active()
        ->select(['taxon_id', 'parent_taxon_id', 'depth', 'hierarchy_path'])
        ->get()
        ->keyBy('taxon_id');

    foreach ($versions as $version) {
        $visited = [$version->taxon_id => true];
        $parentId = $version->parent_taxon_id;

        while ($parentId !== null) {
            expect(isset($visited[$parentId]))->toBeFalse();
            $visited[$parentId] = true;
            $parentId = $versions->get($parentId)?->parent_taxon_id;
        }

        expect(substr_count((string) $version->hierarchy_path, '/'))
            ->toBe($version->depth);
    }
});

test('catalogue of life source registration records provenance without pretending a snapshot was imported', function () {
    $this->seed(ForumSystemSeeder::class);

    $source = TaxonSource::query()
        ->where('stable_key', 'catalogue-of-life-base')
        ->firstOrFail();

    expect($source->version)->toBe('2026-07-14')
        ->and($source->license)->toContain('Attribution 4.0')
        ->and($source->checksum)->toBeNull()
        ->and($source->active_taxon_import_id)->toBeNull()
        ->and($source->metadata['requires_local_snapshot'])->toBeTrue();
});
