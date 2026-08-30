<?php

declare(strict_types=1);

use App\Models\AdoptionCase;
use App\Models\Booking;
use App\Models\DomesticClassification;
use App\Models\ForumCategory;
use App\Models\Order;
use App\Models\PetProfile;
use App\Models\SearchCase;
use App\Models\Sighting;
use App\Models\TaxonImport;
use App\Models\TaxonSource;
use App\Models\TaxonVersion;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RepresentativeDomainSeeder;
use Database\Seeders\RepresentativeFieldCoverageSeeder;
use Database\Seeders\RepresentativeModelManifest;
use Database\Seeders\SearchSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Fixtures\DatabaseSeedCoverage;

use function Pest\Laravel\seed;

test('root seed covers every required field and records every intentional nullable exemption', function (): void {
    Storage::fake('local');

    seed(DatabaseSeeder::class);

    $unpopulatedRequired = [];
    $unclassifiedNullable = [];

    foreach (RepresentativeModelManifest::classes() as $modelClass) {
        $table = (new $modelClass)->getTable();

        foreach (Schema::getColumns($table) as $column) {
            $name = $column['name'];

            if (($column['nullable'] ?? false) !== true) {
                if (DB::table($table)->whereNull($name)->exists()) {
                    $unpopulatedRequired[] = "{$table}.{$name}";
                }

                continue;
            }

            if (DB::table($table)->whereNotNull($name)->exists()) {
                continue;
            }

            if (in_array("{$table}.{$name}", DatabaseSeedCoverage::representativeNullableFields(), true)) {
                continue;
            }

            if (DatabaseSeedCoverage::nullableExemptionReason($table, $name) === null) {
                $unclassifiedNullable[] = "{$table}.{$name}";
            }
        }
    }

    $missingRepresentativeValues = array_values(array_filter(
        DatabaseSeedCoverage::representativeNullableFields(),
        static function (string $qualified): bool {
            [$table, $column] = explode('.', $qualified, 2);

            return ! DB::table($table)->whereNotNull($column)->exists();
        },
    ));

    $missingStructuredValues = array_values(array_filter(
        DatabaseSeedCoverage::structuredFieldsRequiringRepresentativeValues(),
        static function (string $qualified): bool {
            [$table, $column] = explode('.', $qualified, 2);

            return ! DB::table($table)
                ->whereNotNull($column)
                ->whereNotIn($column, ['[]', '{}', 'null', '""'])
                ->exists();
        },
    ));

    expect($unpopulatedRequired)->toBe([])
        ->and($unclassifiedNullable)->toBe([])
        ->and($missingRepresentativeValues)->toBe([])
        ->and($missingStructuredValues)->toBe([]);
});

test('representative coverage preserves unrelated records and derives order parties from its aggregate', function (): void {
    Storage::fake('local');
    $category = ForumCategory::factory()->create([
        'rules' => ['Preserve this user-authored rule.'],
        'permissions' => ['preserve-permission'],
        'metadata' => ['source_payload_sha256' => 'sentinel-source-hash'],
    ]);
    $booking = Booking::factory()->create([
        'documents' => [['name' => 'sentinel-private-document.pdf']],
    ]);
    $classification = DomesticClassification::factory()->create([
        'aliases' => ['Sentinel classification alias'],
        'metadata' => ['source' => 'sentinel-import'],
    ]);

    seed(DatabaseSeeder::class);

    expect($category->refresh()->rules)->toBe(['Preserve this user-authored rule.'])
        ->and($category->permissions)->toBe(['preserve-permission'])
        ->and($category->metadata)->toBe(['source_payload_sha256' => 'sentinel-source-hash'])
        ->and($booking->refresh()->documents)->toBe([['name' => 'sentinel-private-document.pdf']])
        ->and($classification->refresh()->aliases)->toBe(['Sentinel classification alias'])
        ->and($classification->metadata)->toBe(['source' => 'sentinel-import']);

    $scout = PetProfile::query()->where('profile_key', 'pet-scout')->firstOrFail();

    expect($scout->domestic_classification_id)->not->toBe($classification->id)
        ->and($scout->domesticClassification?->stable_key)
        ->toBe('domestic.core.canis-lupus-familiaris');

    $searchCase = SearchCase::query()
        ->where('slug', 'scout-missing-vingis-park')
        ->firstOrFail();
    $unrelatedSighting = Sighting::factory()->confirmed()->create([
        'search_case_id' => $searchCase->id,
        'idempotency_key' => '60000000-0000-4000-8000-000000009999',
        'reporter_key' => 'sentinel-reporter',
        'reporter_name' => 'Sentinel Reporter',
        'photo_url' => 'https://example.test/sentinel.jpg',
        'risk_flags' => ['sentinel-risk'],
    ]);

    seed(RepresentativeFieldCoverageSeeder::class);

    expect($unrelatedSighting->refresh()->only([
        'reporter_key',
        'reporter_name',
        'photo_url',
        'risk_flags',
    ]))->toBe([
        'reporter_key' => 'sentinel-reporter',
        'reporter_name' => 'Sentinel Reporter',
        'photo_url' => 'https://example.test/sentinel.jpg',
        'risk_flags' => ['sentinel-risk'],
    ]);

    $orders = Order::query()
        ->whereIn('reference', ['ORD-DEMO-COMPLETED', 'ORD-DEMO-CANCELLED'])
        ->with(['listing', 'reservation'])
        ->get();

    expect($orders)->toHaveCount(2);

    $orders->each(function (Order $order): void {
        expect($order->buyer_id)->toBe($order->reservation->requester_id)
            ->and($order->buyer_key)->toBe($order->reservation->requester_key)
            ->and($order->buyer_name)->toBe($order->reservation->requester_name)
            ->and($order->seller_id)->toBe($order->listing->owner_id)
            ->and($order->seller_key)->toBe($order->listing->owner_key)
            ->and($order->seller_name)->toBe($order->listing->owner_name);
    });

    $adoptionCase = AdoptionCase::query()
        ->with(['listing:id,owner_id', 'petProfile:id,user_id,name,taxon_id,domestic_classification_id'])
        ->whereHas('listing', static fn ($query) => $query
            ->where('slug', 'gentle-adult-cat-meta-is-ready-for-adoption'))
        ->firstOrFail();

    expect($adoptionCase->petProfile)->not->toBeNull()
        ->and($adoptionCase->petProfile?->name)->toBe($adoptionCase->animal_name)
        ->and($adoptionCase->petProfile?->user_id)->toBe($adoptionCase->listing->owner_id)
        ->and($adoptionCase->petProfile?->taxon_id)->toBe($adoptionCase->taxon_id)
        ->and($adoptionCase->petProfile?->domestic_classification_id)
        ->toBe($adoptionCase->domestic_classification_id);
});

test('search seeding creates canonical sightings without replacing an unrelated sighting', function (): void {
    Storage::fake('local');

    $searchCase = SearchCase::factory()->create([
        'owner_id' => $this->authenticatedUser->id,
        'slug' => 'scout-missing-vingis-park',
    ]);
    $unrelatedSighting = Sighting::factory()->confirmed()->create([
        'search_case_id' => $searchCase->id,
        'idempotency_key' => '60000000-0000-4000-8000-000000009998',
        'reporter_key' => 'preexisting-sentinel-reporter',
        'reporter_name' => 'Preexisting Sentinel Reporter',
        'notes' => 'Preserve this unrelated report.',
    ]);

    seed(SearchSeeder::class);

    expect($unrelatedSighting->refresh()->reporter_key)->toBe('preexisting-sentinel-reporter')
        ->and($unrelatedSighting->reporter_name)->toBe('Preexisting Sentinel Reporter')
        ->and($unrelatedSighting->notes)->toBe('Preserve this unrelated report.')
        ->and($searchCase->sightings()
            ->where('idempotency_key', '60000000-0000-4000-8000-000000000001')
            ->exists())->toBeTrue()
        ->and($searchCase->sightings()
            ->where('idempotency_key', '60000000-0000-4000-8000-000000000002')
            ->exists())->toBeTrue();
});

test('representative taxonomy authorship follows the source active import', function (): void {
    Storage::fake('local');

    seed(DatabaseSeeder::class);

    $source = TaxonSource::query()
        ->where('stable_key', 'platform-core-animal-taxonomy')
        ->firstOrFail();
    $previousVersion = TaxonVersion::query()
        ->where('taxon_source_id', $source->id)
        ->where('taxon_import_id', $source->active_taxon_import_id)
        ->where('source_record_id', 'taxon.core.canis-lupus-familiaris')
        ->firstOrFail();
    $nextImport = TaxonImport::factory()->create([
        'taxon_source_id' => $source->id,
    ]);
    $nextVersion = TaxonVersion::factory()->forImport($nextImport)->create([
        'taxon_id' => $previousVersion->taxon_id,
        'source_record_id' => 'taxon.core.canis-lupus-familiaris',
        'authorship' => null,
        'is_active_version' => true,
    ]);

    $previousVersion->forceFill([
        'authorship' => 'Inactive import sentinel authority',
        'is_active_version' => false,
    ])->save();
    $source->forceFill(['active_taxon_import_id' => $nextImport->id])->save();

    seed(RepresentativeDomainSeeder::class);

    expect($nextVersion->refresh()->authorship)
        ->toBe('Representative taxonomy authority, 2026')
        ->and($previousVersion->refresh()->authorship)
        ->toBe('Inactive import sentinel authority');
});
