<?php

declare(strict_types=1);

use App\Actions\UpdatePetProfile;
use App\Actions\UpdatePetProfileStep;
use App\Enums\PetBirthDatePrecision;
use App\Enums\PetLifeStage;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileCompletionStep;
use App\Enums\PetSpeciesConfidence;
use App\Livewire\Pets\ManagePetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\User;
use App\Services\PetLifeStagePresenter;
use App\Services\PetLifeStageResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function lifeStageProfile(User $owner, array $attributes = []): PetProfile
{
    $profile = PetProfile::factory()->for($owner)->create([
        'status' => 'active',
        'visibility' => 'public',
        'is_discoverable' => true,
        ...$attributes,
    ]);

    PetProfileManager::factory()->for($profile, 'profile')->for($owner)->create([
        'role' => PetManagerRole::PrimaryOwner,
    ]);
    PetProfilePrivacySetting::factory()->for($profile, 'profile')->create([
        'profile_visibility' => 'public',
    ]);

    return $profile;
}

it('stores typed life-stage overrides with actor and observation time', function (): void {
    $this->travelTo('2026-08-04 10:00:00');
    $actor = User::factory()->create();
    $profile = PetProfile::factory()->for($actor)->create([
        'life_stage_override' => PetLifeStage::Senior,
        'life_stage_override_by_user_id' => $actor->id,
        'life_stage_override_at' => now(),
    ]);

    expect(Schema::getColumnListing('pet_profiles'))->toContain(
        'life_stage_override',
        'life_stage_override_by_user_id',
        'life_stage_override_at',
    )
        ->and(collect(Schema::getIndexes('pet_profiles'))->pluck('name'))
        ->toContain('pet_profiles_life_stage_actor_idx')
        ->and(collect(Schema::getForeignKeys('pet_profiles'))
            ->contains(fn (array $key): bool => $key['columns'] === ['life_stage_override_by_user_id']
                && $key['foreign_table'] === 'users'))
        ->toBeTrue()
        ->and($profile->life_stage_override)->toBe(PetLifeStage::Senior)
        ->and($profile->life_stage_override_by_user_id)->toBe($actor->id)
        ->and($profile->life_stage_override_at?->toDateTimeString())->toBe('2026-08-04 10:00:00');
});

it('derives different life stages from the configured animal-group catalog', function (
    string $species,
    string $birthDate,
    string $expected,
): void {
    $this->travelTo('2026-08-04 10:00:00');
    $profile = PetProfile::factory()->create([
        'species' => $species,
        'species_confidence' => PetSpeciesConfidence::Confirmed,
        'birth_date' => $birthDate,
        'birth_date_precision' => PetBirthDatePrecision::Exact,
        'life_stage_override' => null,
    ]);

    expect(app(PetLifeStageResolver::class)->for($profile)->value)->toBe($expected);
})->with([
    'newborn puppy' => ['dog', '2026-07-20', 'newborn'],
    'juvenile cat' => ['cat', '2026-04-04', 'juvenile'],
    'young dog' => ['dog', '2025-08-04', 'young'],
    'adult horse at an age when a dog is senior' => ['horse', '2018-08-04', 'adult'],
    'senior dog' => ['dog', '2018-08-04', 'senior'],
    'adult reptile at an age when a dog is senior' => ['reptile', '2018-08-04', 'adult'],
]);

it('does not invent an automatic stage for uncertain species ages or unsupported groups', function (): void {
    $this->travelTo('2026-08-04 10:00:00');
    $resolver = app(PetLifeStageResolver::class);
    $possibleDog = PetProfile::factory()->make([
        'species' => 'dog',
        'species_confidence' => PetSpeciesConfidence::Possible,
        'birth_date' => '2024-08-04',
        'birth_date_precision' => PetBirthDatePrecision::Exact,
    ]);
    $unknownAge = PetProfile::factory()->make([
        'species' => 'dog',
        'birth_date' => null,
        'birth_date_precision' => PetBirthDatePrecision::Unknown,
    ]);
    $boundaryRange = PetProfile::factory()->make([
        'species' => 'dog',
        'birth_date' => '2024-01-01',
        'birth_date_precision' => PetBirthDatePrecision::Year,
    ]);
    $unsupported = PetProfile::factory()->make([
        'species' => 'other',
        'birth_date' => '2024-08-04',
        'birth_date_precision' => PetBirthDatePrecision::Exact,
    ]);

    expect($resolver->for($possibleDog))->toBe(PetLifeStage::Unknown)
        ->and($resolver->for($unknownAge))->toBe(PetLifeStage::Unknown)
        ->and($resolver->for($boundaryRange))->toBe(PetLifeStage::Unknown)
        ->and($resolver->for($unsupported))->toBe(PetLifeStage::Unknown);
});

it('advances an automatic stage at read time without storing derived state', function (): void {
    $profile = PetProfile::factory()->make([
        'species' => 'dog',
        'species_confidence' => PetSpeciesConfidence::Confirmed,
        'birth_date' => '2024-08-04',
        'birth_date_precision' => PetBirthDatePrecision::Exact,
        'life_stage_override' => null,
    ]);
    $resolver = app(PetLifeStageResolver::class);

    expect($resolver->for($profile, new DateTimeImmutable('2026-08-03')))->toBe(PetLifeStage::Young)
        ->and($resolver->for($profile, new DateTimeImmutable('2026-08-04')))->toBe(PetLifeStage::Adult)
        ->and($profile->life_stage_override)->toBeNull();
});

it('resolves and presents a life stage without issuing a database query', function (): void {
    $profile = PetProfile::factory()->make([
        'species' => 'dog',
        'species_confidence' => PetSpeciesConfidence::Confirmed,
        'birth_date' => '2024-08-04',
        'birth_date_precision' => PetBirthDatePrecision::Exact,
        'life_stage_override' => null,
    ]);
    DB::flushQueryLog();
    DB::enableQueryLog();

    $projection = app(PetLifeStagePresenter::class)->for(
        $profile,
        new DateTimeImmutable('2026-08-04'),
    );

    expect($projection['stage'])->toBe(PetLifeStage::Adult->value)
        ->and(DB::getQueryLog())->toBe([]);
});

it('lets an authorized specialist clarify and clear the life stage through the existing step', function (): void {
    $this->travelTo('2026-08-04 10:00:00');
    $owner = User::factory()->create();
    $specialist = User::factory()->create();
    $profile = lifeStageProfile($owner, [
        'species' => 'bird',
        'birth_date' => null,
        'birth_date_precision' => PetBirthDatePrecision::Unknown,
    ]);
    PetProfileManager::factory()->for($profile, 'profile')->for($specialist)->create([
        'role' => PetManagerRole::Specialist,
        'permission_overrides' => ['grant' => ['edit-basics']],
    ]);

    Livewire::actingAs($specialist)
        ->withQueryParams(['step' => PetProfileCompletionStep::AgeAndSex->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('form.lifeStageOverride', PetLifeStage::Adult->value)
        ->call('saveAgeAndSex')
        ->assertHasNoErrors();

    $profile->refresh();

    expect($profile->life_stage_override)->toBe(PetLifeStage::Adult)
        ->and($profile->life_stage_override_by_user_id)->toBe($specialist->id)
        ->and($profile->life_stage_override_at?->toDateTimeString())->toBe('2026-08-04 10:00:00');

    Livewire::actingAs($specialist)
        ->withQueryParams(['step' => PetProfileCompletionStep::AgeAndSex->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('form.lifeStageOverride', 'auto')
        ->call('saveAgeAndSex')
        ->assertHasNoErrors();

    expect($profile->refresh()->life_stage_override)->toBeNull()
        ->and($profile->life_stage_override_by_user_id)->toBeNull()
        ->and($profile->life_stage_override_at)->toBeNull();
});

it('rejects forged life stages and unauthorized direct mutations', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $profile = lifeStageProfile($owner);
    $this->actingAs($owner);
    $action = app(UpdatePetProfileStep::class);

    expect(fn (): PetProfile => $action->handle(
        $profile,
        PetProfileCompletionStep::AgeAndSex,
        [
            'birth_date_precision' => 'unknown',
            'sex' => 'unknown',
            'reproductive_status' => 'unknown',
            'life_stage_override' => 'medically-verified',
        ],
        $profile->lock_version,
        'invalid-life-stage',
    ))->toThrow(ValidationException::class);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::AgeAndSex->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('form.lifeStageOverride', 'medically-verified')
        ->call('saveAgeAndSex')
        ->assertHasErrors(['form.lifeStageOverride']);

    $this->actingAs($outsider);

    expect(fn (): PetProfile => $action->handle(
        $profile,
        PetProfileCompletionStep::AgeAndSex,
        [
            'birth_date_precision' => 'unknown',
            'sex' => 'unknown',
            'reproductive_status' => 'unknown',
            'life_stage_override' => PetLifeStage::Young->value,
        ],
        $profile->lock_version,
        'unauthorized-life-stage',
    ))->toThrow(ValidationException::class);
});

it('normalizes life-stage overrides through the legacy durable update action', function (): void {
    $owner = User::factory()->create();
    $profile = lifeStageProfile($owner, [
        'birth_date' => null,
        'birth_date_precision' => PetBirthDatePrecision::Unknown,
    ]);
    $this->actingAs($owner);

    $updated = app(UpdatePetProfile::class)->handle($profile->slug, [
        'title' => $profile->name,
        'breed' => $profile->breed,
        'body' => '',
        'life_stage_override' => PetLifeStage::Juvenile->value,
    ]);

    expect($updated->life_stage_override)->toBe(PetLifeStage::Juvenile)
        ->and($updated->life_stage_override_by_user_id)->toBe($owner->id);

    expect(fn (): PetProfile => app(UpdatePetProfile::class)->handle($profile->slug, [
        'title' => $profile->name,
        'breed' => $profile->breed,
        'body' => '',
        'life_stage_override' => 'clinically-proven',
    ]))->toThrow(ValidationException::class);
});

it('keeps repeated life-stage submissions idempotent and preserves original provenance', function (): void {
    $this->travelTo('2026-08-04 10:00:00');
    $owner = User::factory()->create();
    $profile = lifeStageProfile($owner, [
        'birth_date' => null,
        'birth_date_precision' => PetBirthDatePrecision::Unknown,
    ]);
    $this->actingAs($owner);
    $action = app(UpdatePetProfileStep::class);
    $payload = [
        'birth_date_precision' => 'unknown',
        'sex' => 'unknown',
        'reproductive_status' => 'unknown',
        'life_stage_override' => PetLifeStage::Young->value,
    ];

    $updated = $action->handle(
        $profile,
        PetProfileCompletionStep::AgeAndSex,
        $payload,
        $profile->lock_version,
        'same-life-stage-request',
    );
    $recordedAt = $updated->life_stage_override_at?->toDateTimeString();
    $replayed = $action->handle(
        $updated,
        PetProfileCompletionStep::AgeAndSex,
        $payload,
        $updated->lock_version,
        'same-life-stage-request',
    );
    $this->travelTo('2026-08-05 10:00:00');
    $unchanged = $action->handle(
        $replayed,
        PetProfileCompletionStep::AgeAndSex,
        $payload,
        $replayed->lock_version,
        'same-life-stage-new-request',
    );
    $compatibilityUpdate = $action->handle(
        $unchanged,
        PetProfileCompletionStep::AgeAndSex,
        [
            'birth_date_precision' => 'unknown',
            'sex' => 'unknown',
            'reproductive_status' => 'unknown',
        ],
        $unchanged->lock_version,
        'age-sex-without-new-life-stage-field',
    );

    expect($compatibilityUpdate->life_stage_override)->toBe(PetLifeStage::Young)
        ->and($compatibilityUpdate->life_stage_override_by_user_id)->toBe($owner->id)
        ->and($compatibilityUpdate->life_stage_override_at?->toDateTimeString())->toBe($recordedAt)
        ->and($profile->lifecycleEvents()->where('event_type', 'profile-step-updated')->count())->toBe(1);
});

it('renders localized species-aware life-stage and source labels in workspace and public profile', function (string $locale): void {
    $this->travelTo('2026-08-04 10:00:00');
    $owner = User::factory()->create(['locale' => $locale]);
    $profile = lifeStageProfile($owner, [
        'name' => 'Baks',
        'species' => 'dog',
        'birth_date' => '2025-08-04',
        'birth_date_precision' => PetBirthDatePrecision::Exact,
        'life_stage_override' => null,
    ]);
    $this->actingAs($owner);
    app()->setLocale($locale);
    $projection = app(PetLifeStagePresenter::class)->for($profile);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::AgeAndSex->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSeeHtml('id="managed-pet-life-stage"')
        ->assertSee($projection['label'])
        ->assertSee($projection['source_label'])
        ->assertDontSee('pet_profiles.');

    $this->get(route('pets.profile', ['petProfile' => $profile->profile_key]))
        ->assertOk()
        ->assertSee($projection['label'])
        ->assertSee($projection['source_label'])
        ->assertDontSee('pet_profiles.');
})->with(['en', 'lt', 'ru']);
