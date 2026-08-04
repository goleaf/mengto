<?php

declare(strict_types=1);

use App\Actions\UpdatePetProfileStep;
use App\Enums\PetIdentifyingMarkType;
use App\Enums\PetIdentifyingMarkVisibility;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileCompletionStep;
use App\Livewire\Pets\ManagePetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileIdentifyingMark;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\User;
use App\Services\PetIdentifyingMarkPresenter;
use App\Services\PetProfileCompletionPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function identifyingMarkProfile(User $owner, array $attributes = []): PetProfile
{
    $profile = PetProfile::factory()->for($owner)->create([
        'status' => 'active',
        'visibility' => 'public',
        'is_discoverable' => true,
        'species' => 'dog',
        'profile_data' => [],
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

/** @return list<array{id: int|null, type: string, description: string, visibility: string}> */
function identifyingMarkPayload(): array
{
    return [
        [
            'id' => null,
            'type' => PetIdentifyingMarkType::Scar->value,
            'description' => 'Thin crescent scar behind the left ear.',
            'visibility' => PetIdentifyingMarkVisibility::Public->value,
        ],
        [
            'id' => null,
            'type' => PetIdentifyingMarkType::Tattoo->value,
            'description' => 'Private verification tattoo inside the right thigh.',
            'visibility' => PetIdentifyingMarkVisibility::Verification->value,
        ],
    ];
}

it('stores encrypted structured marks with stable identity and an indexed active relation', function (): void {
    $owner = User::factory()->create();
    $profile = identifyingMarkProfile($owner, [
        'profile_data' => ['identifying_marks' => 'Legacy private free text.'],
    ]);
    $this->actingAs($owner);

    $updated = app(UpdatePetProfileStep::class)->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: ['identifying_marks_items' => identifyingMarkPayload()],
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'identifying-marks-store-001',
    );
    $marks = $updated->identifyingMarks()->active()->get();

    expect($marks)->toHaveCount(2)
        ->and($marks->pluck('mark_key')->unique())->toHaveCount(2)
        ->and($marks[0]->type)->toBe(PetIdentifyingMarkType::Scar)
        ->and($marks[0]->visibility)->toBe(PetIdentifyingMarkVisibility::Public)
        ->and($marks[1]->visibility)->toBe(PetIdentifyingMarkVisibility::Verification)
        ->and(data_get($updated->profile_data, 'identifying_marks'))->toBe('Legacy private free text.')
        ->and((string) DB::table('pet_profile_identifying_marks')->whereKey($marks[0]->id)->value('description'))
        ->not->toContain('crescent scar')
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->where('event_type', 'profile-step-updated')
            ->count())->toBe(1);
});

it('rejects forged structured mark values at the action boundary', function (array $marks): void {
    $owner = User::factory()->create();
    $profile = identifyingMarkProfile($owner);
    $this->actingAs($owner);

    expect(fn (): PetProfile => app(UpdatePetProfileStep::class)->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: ['identifying_marks_items' => $marks],
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'identifying-marks-invalid-'.md5(serialize($marks)),
    ))->toThrow(ValidationException::class);

    expect(PetProfileIdentifyingMark::query()->count())->toBe(0)
        ->and($profile->refresh()->lock_version)->toBe(1);
})->with([
    'unknown type' => [[[
        'id' => null,
        'type' => 'medical-diagnosis',
        'description' => 'Visible detail.',
        'visibility' => 'public',
    ]]],
    'unsupported friend audience' => [[[
        'id' => null,
        'type' => 'scar',
        'description' => 'Visible detail.',
        'visibility' => 'friends',
    ]]],
    'empty description' => [[[
        'id' => null,
        'type' => 'scar',
        'description' => '   ',
        'visibility' => 'public',
    ]]],
    'overlong description' => [[[
        'id' => null,
        'type' => 'scar',
        'description' => Str::repeat('a', 501),
        'visibility' => 'public',
    ]]],
    'non-list input' => [['not-a-list']],
]);

it('updates and retires marks without accepting an id owned by another pet', function (): void {
    $owner = User::factory()->create();
    $profile = identifyingMarkProfile($owner);
    $other = identifyingMarkProfile($owner, [
        'profile_key' => 'pet-other-identifying-marks',
        'slug' => 'other-identifying-marks',
    ]);
    $foreign = PetProfileIdentifyingMark::factory()->for($other, 'profile')->create();
    $this->actingAs($owner);
    $action = app(UpdatePetProfileStep::class);

    $first = $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: ['identifying_marks_items' => identifyingMarkPayload()],
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'identifying-marks-update-001',
    );
    $stored = $first->identifyingMarks()->active()->get();

    expect(fn (): PetProfile => $action->handle(
        profile: $first,
        step: PetProfileCompletionStep::Appearance,
        data: ['identifying_marks_items' => [[
            'id' => $foreign->id,
            'type' => 'scar',
            'description' => 'Forged cross-pet update.',
            'visibility' => 'public',
        ]]],
        expectedLockVersion: $first->lock_version,
        idempotencyKey: 'identifying-marks-cross-pet-001',
    ))->toThrow(ValidationException::class);

    $updated = $action->handle(
        profile: $first,
        step: PetProfileCompletionStep::Appearance,
        data: ['identifying_marks_items' => [[
            'id' => $stored[0]->id,
            'type' => PetIdentifyingMarkType::EarFeature->value,
            'description' => 'Left ear folds inward at the tip.',
            'visibility' => PetIdentifyingMarkVisibility::Verification->value,
        ]]],
        expectedLockVersion: $first->lock_version,
        idempotencyKey: 'identifying-marks-update-002',
    );

    expect($updated->identifyingMarks()->active()->count())->toBe(1)
        ->and($stored[0]->refresh()->type)->toBe(PetIdentifyingMarkType::EarFeature)
        ->and($stored[0]->description)->toBe('Left ear folds inward at the tip.')
        ->and($stored[1]->refresh()->retired_at)->not->toBeNull();
});

it('keeps replayed saves idempotent and denies an unmanaged profile', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $profile = identifyingMarkProfile($owner);
    $this->actingAs($owner);
    $action = app(UpdatePetProfileStep::class);

    $first = $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: ['identifying_marks_items' => identifyingMarkPayload()],
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'identifying-marks-replay-001',
    );
    $replayed = $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: ['identifying_marks_items' => []],
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'identifying-marks-replay-001',
    );

    expect($replayed->lock_version)->toBe($first->lock_version)
        ->and($profile->identifyingMarks()->active()->count())->toBe(2)
        ->and($profile->lifecycleEvents()
            ->where('event_type', 'profile-step-updated')
            ->count())->toBe(1);

    $this->actingAs($outsider);

    expect(fn (): PetProfile => $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: ['identifying_marks_items' => []],
        expectedLockVersion: $first->lock_version,
        idempotencyKey: 'identifying-marks-denied-001',
    ))->toThrow(ValidationException::class);
});

it('offers a localized accessible editor and restores saved structured marks', function (string $locale): void {
    $owner = User::factory()->create(['locale' => $locale]);
    $profile = identifyingMarkProfile($owner);
    app()->setLocale($locale);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSeeHtml('id="managed-pet-identifying-marks-list"')
        ->assertSee(__('pet_profiles.identifying_marks.title'))
        ->call('addIdentifyingMark')
        ->assertSee(PetIdentifyingMarkType::Scar->label())
        ->assertSee(PetIdentifyingMarkVisibility::Verification->label())
        ->assertDontSee('pet_profiles.identifying_marks')
        ->set('form.identifyingMarkItems.0.type', PetIdentifyingMarkType::EarFeature->value)
        ->set('form.identifyingMarkItems.0.description', 'Left ear folds inward at the tip.')
        ->set('form.identifyingMarkItems.0.visibility', PetIdentifyingMarkVisibility::Public->value)
        ->call('saveAppearance')
        ->assertHasNoErrors()
        ->assertSet('feedback', __('pet_profiles.feedback.appearance_saved'));

    $profile->refresh();

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSet('form.identifyingMarkItems.0.type', PetIdentifyingMarkType::EarFeature->value)
        ->assertSet('form.identifyingMarkItems.0.description', 'Left ear folds inward at the tip.')
        ->assertSet('form.identifyingMarkItems.0.visibility', PetIdentifyingMarkVisibility::Public->value);
})->with(['en', 'lt', 'ru']);

it('retires a removed mark through the Livewire workspace', function (): void {
    $owner = User::factory()->create();
    $profile = identifyingMarkProfile($owner);
    $mark = PetProfileIdentifyingMark::factory()->for($profile, 'profile')->create();

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->call('removeIdentifyingMark', 0)
        ->call('saveAppearance')
        ->assertHasNoErrors();

    expect($mark->refresh()->retired_at)->not->toBeNull();
});

it('projects only public marks without queries and never exposes verification proof', function (string $locale): void {
    $owner = User::factory()->create(['locale' => $locale]);
    $profile = identifyingMarkProfile($owner);
    PetProfileIdentifyingMark::factory()->for($profile, 'profile')->create([
        'type' => PetIdentifyingMarkType::Scar,
        'description' => 'Thin crescent scar behind the left ear.',
        'visibility' => PetIdentifyingMarkVisibility::Public,
        'position' => 0,
    ]);
    PetProfileIdentifyingMark::factory()->for($profile, 'profile')->create([
        'type' => PetIdentifyingMarkType::Tattoo,
        'description' => 'Private verification tattoo inside the right thigh.',
        'visibility' => PetIdentifyingMarkVisibility::Verification,
        'position' => 1,
    ]);
    $profile->load(['activeIdentifyingMarks' => fn ($query) => $query->select([
        'id',
        'mark_key',
        'pet_profile_id',
        'type',
        'description',
        'visibility',
        'position',
    ])]);
    app()->setLocale($locale);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $presented = app(PetIdentifyingMarkPresenter::class)->publicFor($profile);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($queries)->toBe([])
        ->and($presented)->toHaveCount(1)
        ->and($presented[0]['type'])->toBe(PetIdentifyingMarkType::Scar->label())
        ->and($presented[0]['description'])->toBe('Thin crescent scar behind the left ear.');

    $this->actingAs($owner)
        ->get(route('pets.profile', ['petProfile' => $profile->profile_key]))
        ->assertOk()
        ->assertSee(__('pet_profiles.identifying_marks.public_title'))
        ->assertSee('Thin crescent scar behind the left ear.')
        ->assertDontSee('Private verification tattoo inside the right thigh.')
        ->assertDontSee('pet_profiles.identifying_marks');
})->with(['en', 'lt', 'ru']);

it('renders the real appearance route with strict missing-attribute protection enabled', function (): void {
    $owner = User::factory()->create();
    $profile = identifyingMarkProfile($owner);
    $this->actingAs($owner);

    Model::preventAccessingMissingAttributes();

    try {
        $this->get(route('pets.manage.show', [
            'petProfile' => $profile->profile_key,
            'step' => PetProfileCompletionStep::Appearance->value,
        ]))
            ->assertOk()
            ->assertSeeHtml('id="managed-pet-identifying-marks-list"');
    } finally {
        Model::preventAccessingMissingAttributes(false);
    }
});

it('treats an active structured mark as appearance completion data', function (): void {
    $owner = User::factory()->create();
    $profile = identifyingMarkProfile($owner);
    PetProfileIdentifyingMark::factory()->for($profile, 'profile')->create();
    $profile->loadExists('activeIdentifyingMarks');

    $appearance = collect(app(PetProfileCompletionPresenter::class)->present(
        $profile,
        PetProfileCompletionStep::Appearance,
    ))->firstWhere('value', PetProfileCompletionStep::Appearance->value);

    expect($appearance['complete'])->toBeTrue();
});
