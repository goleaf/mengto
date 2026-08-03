<?php

declare(strict_types=1);

use App\Actions\RecordPetProfileFact;
use App\Actions\UpdatePetProfileStep;
use App\Enums\PetEvidenceStatus;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileCompletionStep;
use App\Enums\PetProfileVisibility;
use App\Livewire\Pets\ManagePetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileFact;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function progressivePetProfile(User $owner, array $attributes = []): PetProfile
{
    $profile = PetProfile::factory()->for($owner)->create([
        'status' => 'draft',
        'visibility' => 'private',
        'is_discoverable' => false,
        'profile_data' => [],
        ...$attributes,
    ]);

    PetProfileManager::factory()->for($profile, 'profile')->for($owner)->create([
        'role' => PetManagerRole::PrimaryOwner,
    ]);
    PetProfilePrivacySetting::factory()->for($profile, 'profile')->create();

    return $profile;
}

it('renders twelve central url-backed steps and only the selected step body', function (): void {
    $owner = User::factory()->create();
    $profile = progressivePetProfile($owner);

    $component = Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSet('step', PetProfileCompletionStep::Appearance->value)
        ->assertSeeHtml('data-pet-step-navigation')
        ->assertSeeHtml('overflow-x-auto')
        ->assertSeeHtml('aria-current="step"')
        ->assertSeeHtml('id="pet-step-appearance"')
        ->assertDontSeeHtml('id="pet-step-basics"');

    foreach (PetProfileCompletionStep::cases() as $step) {
        $component->assertSee($step->label());
    }
});

it('normalizes an untrusted step query and skips without mutating the profile', function (): void {
    $owner = User::factory()->create();
    $profile = progressivePetProfile($owner);
    $beforeVersion = $profile->lock_version;
    $beforeEvents = PetProfileLifecycleEvent::query()
        ->where('pet_profile_id', $profile->id)
        ->count();

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => 'not-a-real-step'])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSet('step', PetProfileCompletionStep::Basics->value)
        ->set('step', 'still-not-a-real-step')
        ->assertSet('step', PetProfileCompletionStep::Basics->value)
        ->call('goToStep', PetProfileCompletionStep::Photos->value)
        ->assertSet('step', PetProfileCompletionStep::Photos->value)
        ->assertHasNoErrors();

    expect($profile->refresh()->lock_version)->toBe($beforeVersion)
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->count())->toBe($beforeEvents);
});

it('saves each descriptive section independently and preserves other sections', function (): void {
    $owner = User::factory()->create();
    $profile = progressivePetProfile($owner, [
        'name' => 'Luna',
        'species' => 'cat',
        'breed' => 'Original breed',
        'profile_data' => ['story' => 'Original story'],
    ]);

    $component = Livewire::actingAs($owner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('form.name', 'Luna Nova')
        ->set('form.species', 'cat')
        ->call('saveBasics')
        ->assertHasNoErrors();

    expect($profile->refresh()->name)->toBe('Luna Nova')
        ->and($profile->breed)->toBe('Original breed')
        ->and(data_get($profile->profile_data, 'story'))->toBe('Original story');

    $component
        ->set('form.birthDate', '2020-05-10')
        ->set('form.birthDatePrecision', 'estimated')
        ->set('form.sex', 'female')
        ->set('form.reproductiveStatus', 'spayed')
        ->call('saveAgeAndSex')
        ->set('form.breed', 'Mixed ancestry')
        ->call('saveBreedAndOrigin')
        ->set('form.appearanceSummary', 'Small black cat with green eyes.')
        ->set('form.identifyingMarks', 'White mark on the left paw.')
        ->call('saveAppearance')
        ->set('form.bio', 'Calm indoors and curious outside.')
        ->set('form.temperamentSummary', 'Needs slow introductions.')
        ->call('saveCharacter')
        ->set('form.socialPreferences', 'Prefers calm adult cats.')
        ->set('form.meetingPreferences', 'Short supervised meetings.')
        ->call('saveSocialPreferences')
        ->set('form.locationLabel', 'Vilnius area')
        ->set('form.locationPrecision', 'city')
        ->call('saveLocation')
        ->assertHasNoErrors();

    $profile->refresh();

    expect($profile->birth_date?->toDateString())->toBe('2020-05-10')
        ->and($profile->breed)->toBe('Mixed ancestry')
        ->and(data_get($profile->profile_data, 'appearance_summary'))->toBe('Small black cat with green eyes.')
        ->and(data_get($profile->profile_data, 'identifying_marks'))->toBe('White mark on the left paw.')
        ->and(data_get($profile->profile_data, 'story'))->toBe('Calm indoors and curious outside.')
        ->and(data_get($profile->profile_data, 'temperament_summary'))->toBe('Needs slow introductions.')
        ->and(data_get($profile->profile_data, 'social_preferences'))->toBe('Prefers calm adult cats.')
        ->and(data_get($profile->profile_data, 'meeting_preferences'))->toBe('Short supervised meetings.')
        ->and(data_get($profile->profile_data, 'location_label'))->toBe('Vilnius area')
        ->and(data_get($profile->profile_data, 'location_precision'))->toBe('city');
});

it('keeps partial updates idempotent and rejects stale writes', function (): void {
    $owner = User::factory()->create();
    $profile = progressivePetProfile($owner, ['lock_version' => 4]);
    actingAs($owner);
    $action = app(UpdatePetProfileStep::class);

    $first = $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: ['appearance_summary' => 'Brindle coat', 'identifying_marks' => ''],
        expectedLockVersion: 4,
        idempotencyKey: 'pet-step-appearance-001',
    );
    $replayed = $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: ['appearance_summary' => 'Ignored replay', 'identifying_marks' => ''],
        expectedLockVersion: 4,
        idempotencyKey: 'pet-step-appearance-001',
    );

    expect($first->lock_version)->toBe(5)
        ->and($replayed->id)->toBe($first->id)
        ->and(data_get($replayed->profile_data, 'appearance_summary'))->toBe('Brindle coat')
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->where('event_type', 'profile-step-updated')
            ->count())->toBe(1);

    expect(fn (): PetProfile => $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Location,
        data: ['location_label' => 'Kaunas', 'location_precision' => 'city'],
        expectedLockVersion: 4,
        idempotencyKey: 'pet-step-location-stale',
    ))->toThrow(ValidationException::class);
});

it('autosaves the active descriptive step and restores it after reopening the workspace', function (): void {
    $owner = User::factory()->create();
    $profile = progressivePetProfile($owner, [
        'name' => 'Luna',
        'profile_data' => [],
    ]);

    $component = Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSeeHtml('wire:change="autoSaveStep(\'appearance\', $event.currentTarget.dataset.petProfileAutosaveRevision)"')
        ->assertSeeHtml('data-pet-profile-autosave-step="appearance"')
        ->assertSeeHtml('wire:target="autoSaveStep"');
    $initialIdempotencyKey = $component->get('stepIdempotencyKey');

    $component->set('form.appearanceSummary', 'Silver coat with a dark tail.')
        ->set('form.identifyingMarks', 'Small white patch below the chin.')
        ->call('autoSaveStep', PetProfileCompletionStep::Appearance->value, '7')
        ->assertHasNoErrors()
        ->assertDispatched(
            'pet-profile-autosave-completed',
            step: PetProfileCompletionStep::Appearance->value,
            revision: '7',
        )
        ->assertSet('feedback', __('pet_profiles.feedback.appearance_saved'));

    $profile->refresh();

    expect(data_get($profile->profile_data, 'appearance_summary'))
        ->toBe('Silver coat with a dark tail.')
        ->and(data_get($profile->profile_data, 'identifying_marks'))
        ->toBe('Small white patch below the chin.')
        ->and($component->get('stepIdempotencyKey'))->not->toBe($initialIdempotencyKey)
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->where('event_type', 'profile-step-updated')
            ->count())->toBe(1);

    $component
        ->call('autoSaveStep', PetProfileCompletionStep::Appearance->value, ['untrusted'])
        ->assertHasNoErrors()
        ->assertDispatched(
            'pet-profile-autosave-completed',
            step: PetProfileCompletionStep::Appearance->value,
            revision: null,
        );

    $savedVersion = $profile->lock_version;
    $component
        ->call('saveAppearance')
        ->assertHasNoErrors();

    expect($profile->refresh()->lock_version)->toBe($savedVersion)
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->where('event_type', 'profile-step-updated')
            ->count())->toBe(1);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSet('form.appearanceSummary', 'Silver coat with a dark tail.')
        ->assertSet('form.identifyingMarks', 'Small white patch below the chin.')
        ->call('autoSaveStep', PetProfileCompletionStep::Character->value)
        ->assertHasErrors(['step']);

    expect(PetProfileLifecycleEvent::query()
        ->where('pet_profile_id', $profile->id)
        ->where('event_type', 'profile-step-updated')
        ->count())->toBe(1);
});

it('keeps the autosave key stable when validation fails', function (): void {
    $owner = User::factory()->create();
    $profile = progressivePetProfile($owner, ['name' => 'Luna']);
    $component = Livewire::actingAs($owner)
        ->test(ManagePetProfile::class, ['petProfile' => $profile]);
    $idempotencyKey = $component->get('stepIdempotencyKey');

    $component
        ->set('form.name', '')
        ->call('autoSaveStep', PetProfileCompletionStep::Basics->value)
        ->assertHasErrors(['form.name'])
        ->assertNotDispatched('pet-profile-autosave-completed');

    expect($component->get('stepIdempotencyKey'))->toBe($idempotencyKey)
        ->and($profile->refresh()->name)->toBe('Luna')
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->where('event_type', 'profile-step-updated')
            ->doesntExist())->toBeTrue();
});

it('only acknowledges a bounded numeric client revision after autosave', function (
    ?string $clientRevision,
    ?string $expectedRevision,
): void {
    $owner = User::factory()->create();
    $profile = progressivePetProfile($owner, ['profile_data' => []]);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('form.appearanceSummary', 'Revision acknowledgement boundary.')
        ->call('autoSaveStep', PetProfileCompletionStep::Appearance->value, $clientRevision)
        ->assertHasNoErrors()
        ->assertDispatched(
            'pet-profile-autosave-completed',
            step: PetProfileCompletionStep::Appearance->value,
            revision: $expectedRevision,
        );
})->with([
    'valid revision' => ['42', '42'],
    'zero revision' => ['0', null],
    'negative revision' => ['-1', null],
    'non-numeric revision' => ['revision', null],
    'oversized revision' => ['12345678901', null],
    'absent revision' => [null, null],
]);

it('wires autosave only to the seven descriptive steps', function (
    PetProfileCompletionStep $step,
    bool $supportsAutosave,
): void {
    $owner = User::factory()->create();
    $profile = progressivePetProfile($owner);
    $component = Livewire::actingAs($owner)
        ->withQueryParams(['step' => $step->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile]);
    $binding = 'wire:change="autoSaveStep(\''.$step->value.'\', $event.currentTarget.dataset.petProfileAutosaveRevision)"';

    if ($supportsAutosave) {
        $component
            ->assertSeeHtml($binding)
            ->assertSeeHtml('data-pet-profile-autosave-step="'.$step->value.'"')
            ->assertSeeHtml('data-pet-autosave-status');

        return;
    }

    $component
        ->assertDontSeeHtml($binding)
        ->call('autoSaveStep', $step->value)
        ->assertHasErrors(['step']);
})->with(array_map(
    static fn (PetProfileCompletionStep $step): array => [$step, $step->supportsAutosave()],
    PetProfileCompletionStep::cases(),
));

it('keeps reconnect recovery in page memory instead of browser storage', function (): void {
    $adapter = file_get_contents(resource_path('js/pet-profile-autosave-recovery.js'));
    $entrypoint = file_get_contents(resource_path('js/app.js'));

    expect($adapter)
        ->toContain("window.addEventListener('online', retryPendingForms)")
        ->toContain("window.addEventListener('pet-profile-autosave-completed', clearCompletedStep)")
        ->toContain("form.dispatchEvent(new Event('change', { bubbles: true }))")
        ->not->toContain('localStorage')
        ->not->toContain('sessionStorage')
        ->not->toContain('indexedDB')
        ->and($entrypoint)->toContain("import './pet-profile-autosave-recovery';");
});

it('stores one private encrypted microchip record and enforces the critical permission', function (): void {
    $owner = User::factory()->create();
    $profile = progressivePetProfile($owner);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Documents->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('documentsForm.microchipStatus', 'chipped')
        ->set('documentsForm.microchipIdentifier', '981020001234567')
        ->set('documentsForm.documentsState', 'available')
        ->call('saveDocuments')
        ->assertHasNoErrors();

    $fact = PetProfileFact::query()->sole();

    expect($fact->fact_key)->toBe('microchip-record')
        ->and($fact->visibility->value)->toBe('private')
        ->and($fact->value)->toBe([
            'status' => 'chipped',
            'identifier' => '981020001234567',
            'documents_state' => 'available',
        ])
        ->and((string) $fact->getRawOriginal('value'))->not->toContain('981020001234567');

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Documents->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile->refresh()])
        ->assertSet('documentsForm.microchipIdentifier', '')
        ->assertDontSee('981020001234567')
        ->set('documentsForm.documentsState', 'add-later')
        ->call('saveDocuments')
        ->assertHasNoErrors();

    $retainedFact = PetProfileFact::query()
        ->where('pet_profile_id', $profile->id)
        ->where('is_current', true)
        ->sole();

    expect($retainedFact->value['identifier'])->toBe('981020001234567')
        ->and($retainedFact->value['documents_state'])->toBe('add-later');

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Documents->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile->refresh()])
        ->assertSet('documentsForm.microchipIdentifier', '')
        ->set('documentsForm.microchipStatus', 'not-chipped')
        ->call('saveDocuments')
        ->assertHasNoErrors();

    $currentFact = PetProfileFact::query()
        ->where('pet_profile_id', $profile->id)
        ->where('is_current', true)
        ->sole();

    expect($fact->refresh()->is_current)->toBeFalse()
        ->and($fact->retired_at)->not->toBeNull()
        ->and($retainedFact->refresh()->is_current)->toBeFalse()
        ->and($currentFact->value['status'])->toBe('not-chipped')
        ->and($currentFact->value['identifier'])->toBeNull();

    $sitter = User::factory()->create();
    PetProfileManager::factory()->for($profile, 'profile')->for($sitter)->create([
        'role' => PetManagerRole::Sitter,
    ]);

    actingAs($sitter);
    expect(fn (): PetProfileFact => app(RecordPetProfileFact::class)->handle(
        profile: $profile->refresh(),
        factKey: 'microchip-record',
        value: [
            'status' => 'not-chipped',
            'identifier' => null,
            'documents_state' => 'none',
        ],
        precision: 'unknown',
        sourceType: 'owner',
        sourceReference: null,
        verificationStatus: PetEvidenceStatus::Unverified,
        visibility: PetProfileVisibility::Private,
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'pet-documents-sitter-denied',
    ))->toThrow(AuthorizationException::class);

    expect(PetProfileFact::query()->count())->toBe(3);

    $coOwner = User::factory()->create();
    PetProfileManager::factory()->for($profile, 'profile')->for($coOwner)->create([
        'role' => PetManagerRole::CoOwner,
    ]);

    Livewire::actingAs($coOwner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Documents->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile->refresh()])
        ->assertOk()
        ->assertSee(__('pet_profiles.completion.documents_unavailable_title'))
        ->assertDontSeeHtml('id="pet-microchip-identifier"')
        ->assertDontSee('981020001234567');
});

it('keeps management queries bounded as unrelated profile history grows', function (): void {
    $owner = User::factory()->create();
    $profile = progressivePetProfile($owner);

    $queryCount = function () use ($owner, $profile): int {
        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::actingAs($owner)
            ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
            ->test(ManagePetProfile::class, ['petProfile' => $profile->refresh()])
            ->assertOk();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    $before = $queryCount();

    PetProfileLifecycleEvent::factory()->count(30)->for($profile, 'profile')->create();
    PetProfileFact::factory()->count(30)->for($profile, 'profile')->create([
        'is_current' => false,
        'current_key' => null,
    ]);

    expect($queryCount())->toBeLessThanOrEqual($before + 1);
});
