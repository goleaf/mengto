<?php

declare(strict_types=1);

use App\Actions\ActivateTaxonomyImport;
use App\Actions\AnalyzeTaxonomySnapshot;
use App\Actions\ProcessTaxonomyImportChunk;
use App\Actions\RollbackTaxonomyImport;
use App\Actions\ValidateTaxonomyImport;
use App\Enums\TaxonImportState;
use App\Models\TaxonImport;
use App\Models\TaxonName;
use App\Models\TaxonSource;
use App\Models\TaxonVersion;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

function taxonomySnapshot(array $rows): string
{
    $header = [
        'taxonID',
        'parentNameUsageID',
        'acceptedNameUsageID',
        'scientificName',
        'scientificNameWithoutAuthorship',
        'taxonRank',
        'taxonomicStatus',
        'vernacularName',
        'language',
    ];
    $lines = [implode(',', $header)];

    foreach ($rows as $row) {
        $lines[] = implode(',', array_map(
            static fn (mixed $value): string => '"'.str_replace('"', '""', (string) $value).'"',
            $row,
        ));
    }

    return implode("\n", $lines)."\n";
}

function completeTaxonomyImport(
    TaxonSource $source,
    string $path,
    string $version,
    int $chunkSize = 2,
): TaxonImport {
    $import = app(AnalyzeTaxonomySnapshot::class)->handle(
        source: $source,
        sourceVersion: $version,
        disk: 'local',
        path: $path,
    );

    while ($import->fresh()->state->canProcess()) {
        $result = app(ProcessTaxonomyImportChunk::class)->handle($import, $chunkSize);
        $import->refresh();

        if ($result->isComplete) {
            break;
        }
    }

    app(ValidateTaxonomyImport::class)->handle($import);

    return $import->refresh();
}

test('a local snapshot imports in resumable chunks and remains hidden until activation', function () {
    Storage::fake('local');
    Storage::disk('local')->put('taxonomy/snapshots/animals-v1.csv', taxonomySnapshot([
        ['animalia', '', '', 'Animalia', 'Animalia', 'kingdom', 'accepted', 'Animals', 'en'],
        ['chordata', 'animalia', '', 'Chordata', 'Chordata', 'phylum', 'accepted', 'Chordates', 'en'],
        ['canidae', 'chordata', '', 'Canidae', 'Canidae', 'family', 'accepted', 'Dogs and relatives', 'en'],
        ['dog', 'canidae', '', 'Canis lupus familiaris', 'Canis lupus familiaris', 'subspecies', 'accepted', 'Domestic dog', 'en'],
        ['dog-synonym', 'canidae', 'dog', 'Canis familiaris', 'Canis familiaris', 'species', 'synonym', 'Dog', 'en'],
    ]));
    $source = TaxonSource::factory()->create([
        'stable_key' => 'test-catalogue',
        'version' => 'v1',
        'active_taxon_import_id' => null,
    ]);
    $analyze = app(AnalyzeTaxonomySnapshot::class);
    $process = app(ProcessTaxonomyImportChunk::class);
    $import = $analyze->handle(
        source: $source,
        sourceVersion: 'v1',
        disk: 'local',
        path: 'taxonomy/snapshots/animals-v1.csv',
    );

    expect($import->state)->toBe(TaxonImportState::Ready)
        ->and($import->checksum)->toBe(hash(
            'sha256',
            Storage::disk('local')->get('taxonomy/snapshots/animals-v1.csv'),
        ))
        ->and($source->fresh()->active_taxon_import_id)->toBeNull();

    $firstChunk = $process->handle($import, 2);
    $import->refresh();

    expect($firstChunk->isComplete)->toBeFalse()
        ->and($import->processed_rows)->toBe(2)
        ->and($import->current_chunk)->toBe(1)
        ->and($import->resume_token)->not->toBeNull()
        ->and(TaxonVersion::query()->where('is_active_version', true)->count())->toBe(0);

    while ($import->state->canProcess()) {
        $result = $process->handle($import, 2);
        $import->refresh();

        if ($result->isComplete) {
            break;
        }
    }

    expect($import->state)->toBe(TaxonImportState::Validating)
        ->and($import->processed_rows)->toBe(5)
        ->and($import->synonym_rows)->toBe(1);

    $validation = app(ValidateTaxonomyImport::class)->handle($import);
    $import->refresh();

    expect($validation)->toMatchArray([
        'orphans' => 0,
        'unresolved' => 0,
        'roots' => 1,
        'resolved' => 5,
    ])->and($import->state)->toBe(TaxonImportState::Completed)
        ->and(TaxonVersion::query()->where('is_active_version', true)->count())->toBe(0);

    app(ActivateTaxonomyImport::class)->handle($import);
    $import->refresh();
    $source->refresh();
    $synonym = TaxonVersion::query()
        ->active()
        ->where('source_record_id', 'dog-synonym')
        ->with('taxon.acceptedTaxon')
        ->firstOrFail();

    expect($import->state)->toBe(TaxonImportState::Active)
        ->and($source->active_taxon_import_id)->toBe($import->id)
        ->and(TaxonVersion::query()->active()->count())->toBe(5)
        ->and(TaxonName::query()->search('domestic dog')->exists())->toBeTrue()
        ->and($synonym->taxon->resolution_status)->toBe('synonym')
        ->and($synonym->taxon->acceptedTaxon)->not->toBeNull();
});

test('activation preserves curated names archives removed taxa and supports rollback', function () {
    Storage::fake('local');
    Storage::disk('local')->put('taxonomy/snapshots/animals-v1.csv', taxonomySnapshot([
        ['animalia', '', '', 'Animalia', 'Animalia', 'kingdom', 'accepted', 'Animals', 'en'],
        ['dog', 'animalia', '', 'Canis lupus familiaris', 'Canis lupus familiaris', 'subspecies', 'accepted', 'Domestic dog', 'en'],
        ['legacy', 'animalia', '', 'Canis obsolete', 'Canis obsolete', 'species', 'accepted', 'Legacy dog', 'en'],
    ]));
    Storage::disk('local')->put('taxonomy/snapshots/animals-v2.csv', taxonomySnapshot([
        ['animalia', '', '', 'Animalia', 'Animalia', 'kingdom', 'accepted', 'Animals', 'en'],
        ['dog', 'animalia', '', 'Canis lupus familiaris', 'Canis lupus familiaris', 'subspecies', 'accepted', 'Domestic dog', 'en'],
        ['cat', 'animalia', '', 'Felis catus', 'Felis catus', 'species', 'accepted', 'Domestic cat', 'en'],
    ]));
    $source = TaxonSource::factory()->create([
        'stable_key' => 'rollback-catalogue',
        'version' => 'v1',
        'active_taxon_import_id' => null,
    ]);
    $first = completeTaxonomyImport(
        $source,
        'taxonomy/snapshots/animals-v1.csv',
        'v1',
    );
    app(ActivateTaxonomyImport::class)->handle($first);
    $dogTaxon = TaxonVersion::query()
        ->active()
        ->where('source_record_id', 'dog')
        ->firstOrFail()
        ->taxon;
    $legacyTaxon = TaxonVersion::query()
        ->active()
        ->where('source_record_id', 'legacy')
        ->firstOrFail()
        ->taxon;
    $localName = TaxonName::factory()->create([
        'taxon_id' => $dogTaxon->id,
        'taxon_import_id' => null,
        'taxon_source_id' => null,
        'name' => 'Locally curated dog',
        'normalized_name' => 'locally curated dog',
        'is_local_override' => true,
        'is_active' => true,
    ]);
    $second = completeTaxonomyImport(
        $source,
        'taxonomy/snapshots/animals-v2.csv',
        'v2',
    );

    app(ActivateTaxonomyImport::class)->handle($second);

    expect($first->fresh()->state)->toBe(TaxonImportState::Completed)
        ->and($second->fresh()->state)->toBe(TaxonImportState::Active)
        ->and($localName->fresh()->is_active)->toBeTrue()
        ->and($legacyTaxon->fresh()->is_active)->toBeFalse()
        ->and($legacyTaxon->fresh()->archived_at)->not->toBeNull();

    app(RollbackTaxonomyImport::class)->handle($source->fresh(), $first->fresh());

    expect($source->fresh()->active_taxon_import_id)->toBe($first->id)
        ->and($first->fresh()->state)->toBe(TaxonImportState::Active)
        ->and($second->fresh()->state)->toBe(TaxonImportState::RolledBack)
        ->and($legacyTaxon->fresh()->is_active)->toBeTrue()
        ->and($localName->fresh()->is_active)->toBeTrue();
});

test('unsafe paths and invalid snapshots never become active', function () {
    Storage::fake('local');
    $source = TaxonSource::factory()->create([
        'active_taxon_import_id' => null,
    ]);

    expect(fn () => app(AnalyzeTaxonomySnapshot::class)->handle(
        source: $source,
        sourceVersion: 'unsafe',
        disk: 'local',
        path: '../outside.csv',
    ))->toThrow(InvalidArgumentException::class);

    Storage::disk('local')->put('taxonomy/snapshots/invalid.csv', taxonomySnapshot([
        ['', '', '', '', '', 'species', 'accepted', '', ''],
    ]));
    $import = completeTaxonomyImport(
        $source,
        'taxonomy/snapshots/invalid.csv',
        'invalid',
    );

    expect($import->state)->toBe(TaxonImportState::Failed)
        ->and($import->error_rows)->toBe(1)
        ->and($import->issues()->count())->toBe(1)
        ->and($source->fresh()->active_taxon_import_id)->toBeNull()
        ->and(TaxonVersion::query()->active()->count())->toBe(0);
});

test('taxonomy administration is restricted to active administrators', function () {
    $import = TaxonImport::factory()->create();
    $administrator = User::factory()->administrator()->create();
    $ordinary = User::factory()->create();
    $blockedAdministrator = User::factory()->administrator()->blocked()->create();

    expect($administrator->can('process', $import))->toBeTrue()
        ->and($administrator->can('activate', $import))->toBeTrue()
        ->and($ordinary->can('process', $import))->toBeFalse()
        ->and($blockedAdministrator->can('activate', $import))->toBeFalse();
});

test('taxonomy import command is registered', function () {
    expect(Artisan::all())->toHaveKey('taxonomy:import');
});
