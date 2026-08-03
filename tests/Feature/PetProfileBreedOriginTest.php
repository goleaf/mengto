<?php

declare(strict_types=1);

use App\Actions\CreatePetProfile;
use App\Actions\UpdatePetProfile;
use App\Actions\UpdatePetProfileStep;
use App\Enums\PetBreedConfidence;
use App\Enums\PetBreedOriginType;
use App\Enums\PetBreedSource;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileCompletionStep;
use App\Enums\PetProfileStatus;
use App\Livewire\Pets\ManagePetProfile;
use App\Models\DomesticClassification;
use App\Models\PetProfile;
use App\Models\PetProfileBreedOrigin;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\Taxon;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function breedOriginTestProfile(User $owner, array $attributes = []): PetProfile
{
    $profile = PetProfile::factory()->for($owner)->create([
        'status' => PetProfileStatus::Draft,
        'visibility' => 'private',
        'is_discoverable' => false,
        'breed' => null,
        'breed_origin_type' => null,
        ...$attributes,
    ]);

    PetProfileManager::factory()->for($profile, 'profile')->for($owner)->create([
        'role' => PetManagerRole::PrimaryOwner,
    ]);
    PetProfilePrivacySetting::factory()->for($profile, 'profile')->create();

    return $profile;
}

it('stores several mixed breeds with separate confidence source and optional shares', function (): void {
    $owner = User::factory()->create();
    $taxon = Taxon::factory()->create();
    $collie = DomesticClassification::factory()->for($taxon)->create([
        'canonical_name' => 'Border Collie',
        'classification_type' => 'breed',
    ]);
    $profile = breedOriginTestProfile($owner, ['taxon_id' => $taxon->id]);
    actingAs($owner);

    $updated = app(UpdatePetProfileStep::class)->handle(
        profile: $profile,
        step: PetProfileCompletionStep::BreedAndOrigin,
        data: [
            'taxon_id' => $taxon->id,
            'breed_origin_type' => PetBreedOriginType::Mixed->value,
            'breed_origins' => [
                [
                    'origin_key' => null,
                    'domestic_classification_id' => $collie->id,
                    'name' => '',
                    'confidence' => PetBreedConfidence::Confirmed->value,
                    'source' => PetBreedSource::GeneticTest->value,
                    'approximate_share_percent' => 60,
                ],
                [
                    'origin_key' => null,
                    'domestic_classification_id' => null,
                    'name' => 'Labrador Retriever',
                    'confidence' => PetBreedConfidence::OwnerReported->value,
                    'source' => PetBreedSource::OwnerAssumption->value,
                    'approximate_share_percent' => 40,
                ],
            ],
        ],
        expectedLockVersion: 1,
        idempotencyKey: 'breed-origin-mixed-001',
    );

    $origins = $updated->breedOrigins()->oldest('position')->get();

    expect($updated->breed_origin_type)->toBe(PetBreedOriginType::Mixed)
        ->and($updated->breed)->toBe('Border Collie + Labrador Retriever')
        ->and($updated->domestic_classification_id)->toBeNull()
        ->and($origins)->toHaveCount(2)
        ->and($origins->pluck('breed_name')->all())->toBe([
            'Border Collie',
            'Labrador Retriever',
        ])
        ->and($origins->pluck('confidence')->all())->toBe([
            PetBreedConfidence::Confirmed,
            PetBreedConfidence::OwnerReported,
        ])
        ->and($origins->pluck('source')->all())->toBe([
            PetBreedSource::GeneticTest,
            PetBreedSource::OwnerAssumption,
        ])
        ->and($origins->pluck('approximate_share_percent')->all())->toBe([60, 40])
        ->and($origins->pluck('origin_key')->unique())->toHaveCount(2);
});

it('normalizes legacy create and edit paths without pretending that reported text is verified', function (): void {
    $owner = User::factory()->create();
    actingAs($owner);

    $profile = app(CreatePetProfile::class)->handle([
        'title' => 'Milo',
        'category' => 'dog',
        'detail' => 'Collie mix',
        'body' => 'Calm companion.',
        'relationship_role' => PetManagerRole::PrimaryOwner->value,
        'visibility' => 'private',
        'birth_date_precision' => 'unknown',
        'sex' => 'unknown',
        'reproductive_status' => 'unknown',
        'idempotency_key' => 'breed-legacy-create-001',
    ]);

    expect($profile->refresh()->breed_origin_type)->toBe(PetBreedOriginType::Single)
        ->and($profile->breedOrigins()->sole()->confidence)->toBe(PetBreedConfidence::OwnerReported)
        ->and($profile->breedOrigins()->sole()->source)->toBe(PetBreedSource::OwnerAssumption);

    $updated = app(UpdatePetProfile::class)->handle($profile->slug, [
        'title' => 'Milo',
        'category' => 'Labrador mix',
        'detail' => 'Ready for walks',
        'body' => 'Prefers quiet introductions.',
    ]);

    expect($updated->breed)->toBe('Labrador mix')
        ->and($updated->breed_origin_type)->toBe(PetBreedOriginType::Single)
        ->and($updated->breedOrigins()->sole()->breed_name)->toBe('Labrador mix')
        ->and($updated->breedOrigins()->sole()->confidence)->toBe(PetBreedConfidence::OwnerReported)
        ->and($updated->breedOrigins()->sole()->source)->toBe(PetBreedSource::OwnerAssumption);
});

it('keeps relation-only edits optimistic idempotent and authorized', function (): void {
    $owner = User::factory()->create();
    $profile = breedOriginTestProfile($owner, [
        'breed' => 'Collie',
        'breed_origin_type' => PetBreedOriginType::Single,
    ]);
    $origin = PetProfileBreedOrigin::factory()->for($profile, 'profile')->create([
        'breed_name' => 'Collie',
        'confidence' => PetBreedConfidence::OwnerReported,
        'source' => PetBreedSource::Unknown,
        'position' => 0,
    ]);
    $payload = [
        'taxon_id' => null,
        'breed_origin_type' => PetBreedOriginType::Single->value,
        'breed_origins' => [[
            'origin_key' => $origin->origin_key,
            'domestic_classification_id' => null,
            'name' => 'Collie',
            'confidence' => PetBreedConfidence::OwnerReported->value,
            'source' => PetBreedSource::Shelter->value,
            'approximate_share_percent' => null,
        ]],
    ];
    actingAs($owner);
    $action = app(UpdatePetProfileStep::class);

    $updated = $action->handle(
        $profile,
        PetProfileCompletionStep::BreedAndOrigin,
        $payload,
        1,
        'breed-source-only-001',
    );
    $replayed = $action->handle(
        $profile,
        PetProfileCompletionStep::BreedAndOrigin,
        $payload,
        1,
        'breed-source-only-001',
    );

    expect($updated->lock_version)->toBe(2)
        ->and($updated->breed)->toBe('Collie')
        ->and($updated->breedOrigins()->sole()->source)->toBe(PetBreedSource::Shelter)
        ->and($replayed->id)->toBe($updated->id)
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->where('event_type', 'profile-step-updated')
            ->count())->toBe(1);

    $outsider = User::factory()->create();
    actingAs($outsider);

    expect(fn (): PetProfile => $action->handle(
        $profile->refresh(),
        PetProfileCompletionStep::BreedAndOrigin,
        $payload,
        2,
        'breed-source-outsider-001',
    ))->toThrow(ValidationException::class);
});

it('rejects contradictory origin states percentages and foreign taxonomy', function (): void {
    $owner = User::factory()->create();
    $profileTaxon = Taxon::factory()->create();
    $foreignTaxon = Taxon::factory()->create();
    $foreignBreed = DomesticClassification::factory()->for($foreignTaxon)->create([
        'canonical_name' => 'Foreign breed',
        'classification_type' => 'breed',
    ]);
    $profile = breedOriginTestProfile($owner, ['taxon_id' => $profileTaxon->id]);
    actingAs($owner);
    $action = app(UpdatePetProfileStep::class);

    expect(fn (): PetProfile => $action->handle(
        $profile,
        PetProfileCompletionStep::BreedAndOrigin,
        [
            'taxon_id' => $profileTaxon->id,
            'breed_origin_type' => PetBreedOriginType::NoBreed->value,
            'breed_origins' => [[
                'name' => 'Contradiction',
                'confidence' => PetBreedConfidence::Suspected->value,
                'source' => PetBreedSource::Unknown->value,
            ]],
        ],
        1,
        'breed-contradiction-001',
    ))->toThrow(ValidationException::class);

    expect(fn (): PetProfile => $action->handle(
        $profile,
        PetProfileCompletionStep::BreedAndOrigin,
        [
            'taxon_id' => $profileTaxon->id,
            'breed_origin_type' => PetBreedOriginType::Mixed->value,
            'breed_origins' => [
                [
                    'name' => 'First',
                    'confidence' => PetBreedConfidence::Suspected->value,
                    'source' => PetBreedSource::OwnerAssumption->value,
                    'approximate_share_percent' => 70,
                ],
                [
                    'name' => 'Second',
                    'confidence' => PetBreedConfidence::Suspected->value,
                    'source' => PetBreedSource::OwnerAssumption->value,
                    'approximate_share_percent' => 50,
                ],
            ],
        ],
        1,
        'breed-share-overflow-001',
    ))->toThrow(ValidationException::class);

    expect(fn (): PetProfile => $action->handle(
        $profile,
        PetProfileCompletionStep::BreedAndOrigin,
        [
            'taxon_id' => $profileTaxon->id,
            'breed_origin_type' => PetBreedOriginType::Single->value,
            'breed_origins' => [[
                'domestic_classification_id' => $foreignBreed->id,
                'name' => '',
                'confidence' => PetBreedConfidence::Confirmed->value,
                'source' => PetBreedSource::Pedigree->value,
            ]],
        ],
        1,
        'breed-foreign-taxon-001',
    ))->toThrow(ValidationException::class);

    expect($profile->refresh()->lock_version)->toBe(1)
        ->and($profile->breedOrigins()->doesntExist())->toBeTrue();
});

it('offers an accessible localized multi-origin editor and persists unknown mixed origin', function (): void {
    $owner = User::factory()->create();
    $profile = breedOriginTestProfile($owner);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::BreedAndOrigin->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSee(__('pet_profiles.breed_origin.type_label'))
        ->assertSee(__('pet_profiles.breed_origin.types.mixed'))
        ->assertSee(__('pet_profiles.breed_origin.trust_notice'))
        ->assertSeeHtml('id="managed-pet-breed-origin-type"')
        ->assertSeeHtml('wire:target="saveBreedAndOrigin"')
        ->set('form.breedOriginType', PetBreedOriginType::Mixed->value)
        ->assertSet('form.breedOrigins', [])
        ->call('addBreedOrigin')
        ->assertSet('form.breedOrigins', fn (array $origins): bool => count($origins) === 1)
        ->set('form.breedOrigins', [])
        ->call('saveBreedAndOrigin')
        ->assertHasNoErrors();

    expect($profile->refresh()->breed_origin_type)->toBe(PetBreedOriginType::Mixed)
        ->and($profile->breed)->toBeNull()
        ->and($profile->breedOrigins()->doesntExist())->toBeTrue();

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::BreedAndOrigin->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSet('form.breedOriginType', PetBreedOriginType::Mixed->value)
        ->assertSet('form.breedOrigins', []);
});

it('projects source and confidence publicly while keeping legacy breed honest', function (): void {
    $owner = User::factory()->create();
    $profile = breedOriginTestProfile($owner, [
        'status' => PetProfileStatus::Active,
        'visibility' => 'public',
        'is_discoverable' => true,
        'breed' => 'Border Collie',
        'breed_origin_type' => PetBreedOriginType::Single,
    ]);
    PetProfileBreedOrigin::factory()->for($profile, 'profile')->create([
        'breed_name' => 'Border Collie',
        'confidence' => PetBreedConfidence::Confirmed,
        'source' => PetBreedSource::Pedigree,
    ]);
    $legacy = breedOriginTestProfile($owner, [
        'profile_key' => 'pet-legacy-breed',
        'slug' => 'legacy-breed',
        'status' => PetProfileStatus::Active,
        'visibility' => 'public',
        'is_discoverable' => true,
        'breed' => 'Rescue-reported mix',
        'breed_origin_type' => null,
    ]);
    actingAs($owner);

    Model::preventAccessingMissingAttributes();

    try {
        get(route('pets.profile', ['petProfile' => $profile->profile_key]))
            ->assertOk()
            ->assertSee('Border Collie')
            ->assertSee(PetBreedConfidence::Confirmed->label())
            ->assertSee(PetBreedSource::Pedigree->label())
            ->assertSee(__('pet_profiles.breed_origin.public_notice'));

        get(route('pets.profile', ['petProfile' => $legacy->profile_key]))
            ->assertOk()
            ->assertSee('Rescue-reported mix')
            ->assertSee(PetBreedConfidence::OwnerReported->label())
            ->assertSee(PetBreedSource::Unknown->label())
            ->assertDontSee('pet_profiles.breed_origin');
    } finally {
        Model::preventAccessingMissingAttributes(false);
    }
});

it('keeps public breed projection query count bounded as unrelated origins grow', function (): void {
    $owner = User::factory()->create();
    $profile = breedOriginTestProfile($owner, [
        'status' => PetProfileStatus::Active,
        'visibility' => 'public',
        'is_discoverable' => true,
        'breed' => 'Collie',
        'breed_origin_type' => PetBreedOriginType::Single,
    ]);
    PetProfileBreedOrigin::factory()->for($profile, 'profile')->create([
        'breed_name' => 'Collie',
    ]);
    actingAs($owner);

    $queryCount = function () use ($profile): int {
        DB::flushQueryLog();
        DB::enableQueryLog();

        get(route('pets.profile', ['petProfile' => $profile->profile_key]))
            ->assertOk();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    $before = $queryCount();
    PetProfileBreedOrigin::factory()->count(30)->create();

    expect($queryCount())->toBeLessThanOrEqual($before + 1);
});
