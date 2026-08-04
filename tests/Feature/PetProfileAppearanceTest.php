<?php

declare(strict_types=1);

use App\Actions\UpdatePetProfileStep;
use App\Enums\PetAppearanceColor;
use App\Enums\PetAppearancePattern;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileCompletionStep;
use App\Livewire\Pets\ManagePetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\User;
use App\Services\PetAppearancePresenter;
use App\Services\PetProfileCompletionPresenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function appearanceTestProfile(User $owner, array $attributes = []): PetProfile
{
    $profile = PetProfile::factory()->for($owner)->create([
        'status' => 'active',
        'visibility' => 'public',
        'is_discoverable' => true,
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

/** @return array<string, mixed> */
function structuredAppearancePayload(): array
{
    return [
        'primary_color' => PetAppearanceColor::Black->value,
        'additional_colors' => [
            PetAppearanceColor::White->value,
            PetAppearanceColor::Gold->value,
        ],
        'patterns' => [
            PetAppearancePattern::Spots->value,
            PetAppearancePattern::Stripes->value,
            PetAppearancePattern::Gradient->value,
        ],
        'color_details' => 'White chest and a warm gold gradient near the tail.',
        'feather_color_details' => 'Dark wing tips when the feathers are fully grown.',
        'scale_color_details' => 'Gold scales along the lower side.',
        'seasonal_color_changes' => 'The coat becomes lighter in summer.',
        'appearance_summary' => 'Compact build with a short coat.',
        'identifying_marks' => 'Private crescent-shaped mark behind the left ear.',
    ];
}

it('stores a structured encrypted color description while preserving legacy appearance fields', function (): void {
    $owner = User::factory()->create();
    $profile = appearanceTestProfile($owner);
    $this->actingAs($owner);

    $updated = app(UpdatePetProfileStep::class)->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: structuredAppearancePayload(),
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'appearance-structured-001',
    );

    expect(data_get($updated->profile_data, 'appearance'))->toBe([
        'schema_version' => 1,
        'primary_color' => 'black',
        'additional_colors' => ['white', 'gold'],
        'patterns' => ['spots', 'stripes', 'gradient'],
        'color_details' => 'White chest and a warm gold gradient near the tail.',
        'feather_color_details' => 'Dark wing tips when the feathers are fully grown.',
        'scale_color_details' => 'Gold scales along the lower side.',
        'seasonal_color_changes' => 'The coat becomes lighter in summer.',
    ])
        ->and(data_get($updated->profile_data, 'appearance_summary'))
        ->toBe('Compact build with a short coat.')
        ->and(data_get($updated->profile_data, 'identifying_marks'))
        ->toBe('Private crescent-shaped mark behind the left ear.')
        ->and((string) DB::table('pet_profiles')->whereKey($profile->id)->value('profile_data'))
        ->not->toContain('crescent-shaped', 'White chest', 'black')
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->where('event_type', 'profile-step-updated')
            ->count())->toBe(1);
});

it('rejects forged duplicate and oversized color payloads at the action boundary', function (array $override): void {
    $owner = User::factory()->create();
    $profile = appearanceTestProfile($owner);
    $this->actingAs($owner);

    expect(fn (): PetProfile => app(UpdatePetProfileStep::class)->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: [...structuredAppearancePayload(), ...$override],
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'appearance-invalid-'.md5(serialize($override)),
    ))->toThrow(ValidationException::class);

    expect($profile->refresh()->profile_data)->toBe([])
        ->and($profile->lock_version)->toBe(1);
})->with([
    'unknown primary color' => [['primary_color' => 'medical-blue']],
    'primary repeated as an additional color' => [[
        'primary_color' => 'black',
        'additional_colors' => ['black'],
    ]],
    'duplicate additional colors' => [[
        'additional_colors' => ['white', 'white'],
    ]],
    'too many additional colors' => [[
        'additional_colors' => ['white', 'gold', 'brown', 'gray', 'cream'],
    ]],
    'unknown pattern' => [['patterns' => ['camouflage']]],
    'oversized clarification' => [['color_details' => str_repeat('x', 1001)]],
]);

it('keeps appearance mutations authorized idempotent and mutation free for unchanged values', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $profile = appearanceTestProfile($owner);
    $this->actingAs($owner);
    $action = app(UpdatePetProfileStep::class);

    $updated = $action->handle(
        $profile,
        PetProfileCompletionStep::Appearance,
        structuredAppearancePayload(),
        1,
        'appearance-idempotent-001',
    );
    $replayed = $action->handle(
        $updated,
        PetProfileCompletionStep::Appearance,
        structuredAppearancePayload(),
        2,
        'appearance-idempotent-001',
    );
    $unchanged = $action->handle(
        $replayed,
        PetProfileCompletionStep::Appearance,
        structuredAppearancePayload(),
        2,
        'appearance-idempotent-002',
    );

    expect($unchanged->lock_version)->toBe(2)
        ->and($profile->lifecycleEvents()
            ->where('event_type', 'profile-step-updated')
            ->count())->toBe(1);

    $this->actingAs($outsider);

    expect(fn (): PetProfile => $action->handle(
        $unchanged,
        PetProfileCompletionStep::Appearance,
        structuredAppearancePayload(),
        2,
        'appearance-outsider-001',
    ))->toThrow(ValidationException::class);
});

it('offers a localized accessible structured color editor and restores saved state', function (string $locale): void {
    $owner = User::factory()->create(['locale' => $locale]);
    $profile = appearanceTestProfile($owner);
    app()->setLocale($locale);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSeeHtml('id="managed-pet-primary-color"')
        ->assertSeeHtml('id="managed-pet-additional-colors"')
        ->assertSeeHtml('id="managed-pet-color-patterns"')
        ->assertSee(__('pet_profiles.appearance.catalog_notice_title'))
        ->assertSee(PetAppearanceColor::Black->label())
        ->assertSee(PetAppearancePattern::Spots->label())
        ->assertDontSee('pet_profiles.')
        ->set('form.appearancePrimaryColor', 'black')
        ->set('form.appearanceAdditionalColors', ['white', 'gold'])
        ->set('form.appearancePatterns', ['spots', 'gradient'])
        ->set('form.appearanceColorDetails', 'A clear light patch on the chest.')
        ->set('form.appearanceSeasonalColorChanges', 'Lighter in summer.')
        ->call('saveAppearance')
        ->assertHasNoErrors()
        ->assertSet('feedback', __('pet_profiles.feedback.appearance_saved'));

    $profile->refresh();

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSet('form.appearancePrimaryColor', 'black')
        ->assertSet('form.appearanceAdditionalColors', ['white', 'gold'])
        ->assertSet('form.appearancePatterns', ['spots', 'gradient'])
        ->assertSet('form.appearanceColorDetails', 'A clear light patch on the chest.')
        ->assertSet('form.appearanceSeasonalColorChanges', 'Lighter in summer.');
})->with(['en', 'lt', 'ru']);

it('projects localized structured color facts publicly without exposing private identifying marks or adding queries', function (string $locale): void {
    $owner = User::factory()->create(['locale' => $locale]);
    $profile = appearanceTestProfile($owner, [
        'profile_data' => [
            'appearance' => [
                'schema_version' => 1,
                'primary_color' => 'black',
                'additional_colors' => ['white', 'gold'],
                'patterns' => ['spots', 'gradient'],
                'color_details' => 'A light patch on the chest.',
                'feather_color_details' => '',
                'scale_color_details' => '',
                'seasonal_color_changes' => 'Lighter in summer.',
            ],
            'identifying_marks' => 'Private verification mark behind the ear.',
        ],
    ]);
    $this->actingAs($owner);
    app()->setLocale($locale);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $projection = app(PetAppearancePresenter::class)->for($profile);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($projection)->not->toBeNull()
        ->and($projection['primary_color'])->toBe(PetAppearanceColor::Black->label())
        ->and($projection['patterns'])->toContain(PetAppearancePattern::Spots->label())
        ->and($queries)->toBe([]);

    $this->get(route('pets.profile', ['petProfile' => $profile->profile_key]))
        ->assertOk()
        ->assertSee(__('pet_profiles.appearance.public_title'))
        ->assertSee(PetAppearanceColor::Black->label())
        ->assertSee(PetAppearancePattern::Spots->label())
        ->assertSee('A light patch on the chest.')
        ->assertSee('Lighter in summer.')
        ->assertDontSee('Private verification mark behind the ear.')
        ->assertDontSee('pet_profiles.');
})->with(['en', 'lt', 'ru']);

it('preserves legacy appearance text and treats structured color as completion data', function (): void {
    $owner = User::factory()->create();
    $legacy = appearanceTestProfile($owner, [
        'profile_data' => [
            'appearance_summary' => 'Legacy overall appearance.',
            'identifying_marks' => 'Legacy private mark.',
        ],
    ]);
    $structured = appearanceTestProfile($owner, [
        'profile_key' => 'pet-structured-appearance',
        'slug' => 'structured-appearance',
        'profile_data' => [
            'appearance' => [
                'schema_version' => 1,
                'primary_color' => 'gold',
                'additional_colors' => [],
                'patterns' => [],
                'color_details' => '',
                'feather_color_details' => '',
                'scale_color_details' => '',
                'seasonal_color_changes' => '',
            ],
        ],
    ]);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $legacy])
        ->assertSet('form.appearanceSummary', 'Legacy overall appearance.')
        ->assertSet('form.identifyingMarks', 'Legacy private mark.')
        ->assertSet('form.appearancePrimaryColor', '');

    $appearanceStep = collect(app(PetProfileCompletionPresenter::class)->present(
        $structured,
        PetProfileCompletionStep::Appearance,
    ))->firstWhere('value', PetProfileCompletionStep::Appearance->value);

    expect($appearanceStep['complete'])->toBeTrue();
});
