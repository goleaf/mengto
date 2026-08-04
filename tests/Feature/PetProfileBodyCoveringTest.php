<?php

declare(strict_types=1);

use App\Actions\UpdatePetProfileStep;
use App\Enums\PetCoatLength;
use App\Enums\PetCoatTexture;
use App\Enums\PetFeatherType;
use App\Enums\PetManagerRole;
use App\Enums\PetManeType;
use App\Enums\PetProfileCompletionStep;
use App\Enums\PetSeasonalShedding;
use App\Enums\PetUndercoatType;
use App\Livewire\Pets\ManagePetProfile;
use App\Livewire\Pets\PublicPetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\User;
use App\Services\PetBodyCoveringPresenter;
use App\Services\PetBodyCoveringSchema;
use App\Services\PetProfileCompletionPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function bodyCoveringProfile(User $owner, array $attributes = []): PetProfile
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

/** @return array<string, mixed> */
function dogBodyCoveringPayload(array $overrides = []): array
{
    return [
        'coat_length' => PetCoatLength::Short->value,
        'coat_texture' => PetCoatTexture::Wavy->value,
        'undercoat' => PetUndercoatType::Dense->value,
        'hairless' => false,
        'feather_type' => '',
        'skin_condition' => 'Small dry-looking area for the manager to monitor.',
        'mane_type' => '',
        'seasonal_shedding' => PetSeasonalShedding::Heavy->value,
        ...$overrides,
    ];
}

it('stores a structured encrypted species-aware body covering without replacing appearance data', function (): void {
    $owner = User::factory()->create();
    $profile = bodyCoveringProfile($owner, [
        'profile_data' => [
            'appearance_summary' => 'Compact build.',
            'appearance' => ['schema_version' => 1, 'primary_color' => 'black'],
        ],
    ]);
    $this->actingAs($owner);

    $updated = app(UpdatePetProfileStep::class)->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: dogBodyCoveringPayload(),
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'body-covering-store-001',
    );

    expect(data_get($updated->profile_data, 'body_covering'))->toBe([
        'schema_version' => 1,
        'coat_length' => 'short',
        'coat_texture' => 'wavy',
        'undercoat' => 'dense',
        'hairless' => false,
        'feather_type' => null,
        'skin_condition' => 'Small dry-looking area for the manager to monitor.',
        'mane_type' => null,
        'seasonal_shedding' => 'heavy',
    ])
        ->and(data_get($updated->profile_data, 'appearance_summary'))->toBe('Compact build.')
        ->and(data_get($updated->profile_data, 'appearance.primary_color'))->toBe('black')
        ->and((string) $updated->getRawOriginal('profile_data'))
        ->not->toContain('dry-looking', 'wavy', 'heavy')
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->where('event_type', 'profile-step-updated')
            ->count())->toBe(1);
});

it('rejects forged body covering values at the action boundary', function (array $override): void {
    $owner = User::factory()->create();
    $profile = bodyCoveringProfile($owner);
    $this->actingAs($owner);

    expect(fn (): PetProfile => app(UpdatePetProfileStep::class)->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: dogBodyCoveringPayload($override),
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'body-covering-invalid-'.md5(serialize($override)),
    ))->toThrow(ValidationException::class);

    expect($profile->refresh()->profile_data)->toBe([])
        ->and($profile->lock_version)->toBe(1);
})->with([
    'unknown coat length' => [['coat_length' => 'medical-short']],
    'unknown coat texture' => [['coat_texture' => 'impossibly-soft']],
    'unknown undercoat' => [['undercoat' => 'double-plus']],
    'non boolean hairless value' => [['hairless' => 'yes']],
    'hairless coat contradiction' => [['hairless' => true, 'coat_length' => 'short']],
    'unknown shedding state' => [['seasonal_shedding' => 'constant']],
    'overlong private skin note' => [['skin_condition' => Str::repeat('a', 1001)]],
]);

it('drops fields that do not apply to the current species', function (): void {
    $owner = User::factory()->create();
    $profile = bodyCoveringProfile($owner);
    $this->actingAs($owner);

    $updated = app(UpdatePetProfileStep::class)->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: dogBodyCoveringPayload([
            'feather_type' => PetFeatherType::Flight->value,
            'mane_type' => PetManeType::Long->value,
        ]),
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'body-covering-species-prune-001',
    );

    expect(data_get($updated->profile_data, 'body_covering.feather_type'))->toBeNull()
        ->and(data_get($updated->profile_data, 'body_covering.mane_type'))->toBeNull();
});

it('keeps a repeated body covering save idempotent and refuses an unmanaged profile', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $profile = bodyCoveringProfile($owner);
    $this->actingAs($owner);
    $action = app(UpdatePetProfileStep::class);

    $first = $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: dogBodyCoveringPayload(),
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'body-covering-idempotent-001',
    );
    $replayed = $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: dogBodyCoveringPayload(['coat_length' => PetCoatLength::Long->value]),
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'body-covering-idempotent-001',
    );

    expect($replayed->lock_version)->toBe($first->lock_version)
        ->and(data_get($replayed->profile_data, 'body_covering.coat_length'))->toBe('short')
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->where('event_type', 'profile-step-updated')
            ->count())->toBe(1);

    $this->actingAs($stranger);

    expect(fn (): PetProfile => $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: dogBodyCoveringPayload(),
        expectedLockVersion: $first->lock_version,
        idempotencyKey: 'body-covering-denied-001',
    ))->toThrow(ValidationException::class);
});

it('defines distinct descriptive fields for supported species groups', function (): void {
    $schema = app(PetBodyCoveringSchema::class);

    expect($schema->for('dog'))->toBe([
        'coat' => true,
        'feathers' => false,
        'scales' => false,
        'skin' => true,
        'mane' => false,
        'shedding' => true,
    ])->and($schema->for('Dog'))->toBe($schema->for('dog'))
        ->and($schema->for('bird'))->toBe([
            'coat' => false,
            'feathers' => true,
            'scales' => false,
            'skin' => true,
            'mane' => false,
            'shedding' => true,
        ])->and($schema->for('reptile'))->toBe([
            'coat' => false,
            'feathers' => false,
            'scales' => true,
            'skin' => true,
            'mane' => false,
            'shedding' => true,
        ])->and($schema->for('horse'))->toBe([
            'coat' => true,
            'feathers' => false,
            'scales' => false,
            'skin' => true,
            'mane' => true,
            'shedding' => true,
        ]);
});

it('renders and saves only the relevant dog body covering controls', function (): void {
    $owner = User::factory()->create();
    $profile = bodyCoveringProfile($owner);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSee(__('pet_profiles.body_covering.coat_length'))
        ->assertSee(__('pet_profiles.body_covering.undercoat'))
        ->assertDontSee(__('pet_profiles.body_covering.feather_type'))
        ->assertDontSee(__('pet_profiles.appearance.scale_color_details'))
        ->assertDontSee(__('pet_profiles.body_covering.mane_type'))
        ->set('form.bodyCoveringCoatLength', PetCoatLength::Short->value)
        ->set('form.bodyCoveringCoatTexture', PetCoatTexture::Wavy->value)
        ->set('form.bodyCoveringUndercoat', PetUndercoatType::Dense->value)
        ->set('form.bodyCoveringSeasonalShedding', PetSeasonalShedding::Heavy->value)
        ->set('form.bodyCoveringSkinCondition', 'Manager-only visible skin note.')
        ->call('saveAppearance')
        ->assertHasNoErrors();

    expect(data_get($profile->refresh()->profile_data, 'body_covering.coat_texture'))->toBe('wavy')
        ->and(data_get($profile->profile_data, 'body_covering.skin_condition'))
        ->toBe('Manager-only visible skin note.');
});

it('renders the real appearance route with strict missing-attribute protection enabled', function (): void {
    $owner = User::factory()->create();
    $profile = bodyCoveringProfile($owner);
    $this->actingAs($owner);

    Model::preventAccessingMissingAttributes();

    try {
        $this->get(route('pets.manage.show', [
            'petProfile' => $profile->profile_key,
            'step' => PetProfileCompletionStep::Appearance->value,
        ]))
            ->assertOk()
            ->assertSeeHtml('id="managed-pet-body-covering-heading"');
    } finally {
        Model::preventAccessingMissingAttributes(false);
    }
});

it('renders feather, scale, and mane controls only for their relevant species', function (string $species, string $visible, array $hidden): void {
    $owner = User::factory()->create();
    $profile = bodyCoveringProfile($owner, ['species' => $species]);

    $component = Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSee($visible);

    foreach ($hidden as $label) {
        $component->assertDontSee($label);
    }
})->with([
    'bird feathers' => [
        'bird',
        'Feather type',
        ['Coat length', 'Scale color details', 'Mane type'],
    ],
    'reptile scales' => [
        'reptile',
        'Scale color details',
        ['Coat length', 'Feather type', 'Mane type'],
    ],
    'horse mane' => [
        'horse',
        'Mane type',
        ['Feather type', 'Scale color details'],
    ],
]);

it('saves the relevant feather, scale, or mane description through the shared appearance step', function (string $species, string $property, string $value, string $path): void {
    $owner = User::factory()->create();
    $profile = bodyCoveringProfile($owner, ['species' => $species]);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set($property, $value)
        ->call('saveAppearance')
        ->assertHasNoErrors();

    expect(data_get($profile->refresh()->profile_data, $path))->toBe($value);
})->with([
    'bird feather type' => [
        'bird',
        'form.bodyCoveringFeatherType',
        'flight',
        'body_covering.feather_type',
    ],
    'reptile scale coloring' => [
        'reptile',
        'form.appearanceScaleColorDetails',
        'Gold scales along the lower side.',
        'appearance.scale_color_details',
    ],
    'horse mane type' => [
        'horse',
        'form.bodyCoveringManeType',
        'long',
        'body_covering.mane_type',
    ],
]);

it('localizes the species-aware editor and public labels in every supported locale', function (string $locale): void {
    $owner = User::factory()->create(['locale' => $locale]);
    $profile = bodyCoveringProfile($owner, [
        'profile_data' => [
            'body_covering' => [
                'schema_version' => 1,
                'coat_length' => 'short',
                'coat_texture' => 'wavy',
                'undercoat' => 'dense',
                'hairless' => false,
                'feather_type' => null,
                'skin_condition' => '',
                'mane_type' => null,
                'seasonal_shedding' => 'heavy',
            ],
        ],
    ]);
    app()->setLocale($locale);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSee(__('pet_profiles.body_covering.title'))
        ->assertSee(PetCoatLength::Short->label())
        ->assertSee(PetCoatTexture::Wavy->label())
        ->assertDontSee('pet_profiles.body_covering');

    $presented = app(PetBodyCoveringPresenter::class)->for($profile);

    expect($presented['coat_length'])->toBe(PetCoatLength::Short->label())
        ->and($presented['coat_texture'])->toBe(PetCoatTexture::Wavy->label())
        ->and($presented['undercoat'])->toBe(PetUndercoatType::Dense->label())
        ->and($presented['seasonal_shedding'])->toBe(PetSeasonalShedding::Heavy->label());
})->with(['en', 'lt', 'ru']);

it('clears contradictory coat fields when hairless is selected in Livewire', function (): void {
    $owner = User::factory()->create();
    $profile = bodyCoveringProfile($owner);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('form.bodyCoveringCoatLength', 'long')
        ->set('form.bodyCoveringCoatTexture', 'silky')
        ->set('form.bodyCoveringUndercoat', 'dense')
        ->set('form.bodyCoveringHairless', true)
        ->assertSet('form.bodyCoveringCoatLength', '')
        ->assertSet('form.bodyCoveringCoatTexture', '')
        ->assertSet('form.bodyCoveringUndercoat', '')
        ->assertDontSee(__('pet_profiles.body_covering.coat_length'))
        ->call('saveAppearance')
        ->assertHasNoErrors();

    expect(data_get($profile->refresh()->profile_data, 'body_covering.hairless'))->toBeTrue();
});

it('prepares a localized query-free public description without the private skin note', function (): void {
    $owner = User::factory()->create();
    $profile = bodyCoveringProfile($owner, [
        'profile_data' => [
            'body_covering' => [
                'schema_version' => 1,
                'coat_length' => 'short',
                'coat_texture' => 'wavy',
                'undercoat' => 'dense',
                'hairless' => false,
                'feather_type' => null,
                'skin_condition' => 'Private flaky patch.',
                'mane_type' => null,
                'seasonal_shedding' => 'heavy',
            ],
        ],
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();
    app()->setLocale('en');
    $presented = app(PetBodyCoveringPresenter::class)->for($profile);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($queries)->toBe([])
        ->and($presented)->toBe([
            'coat_length' => 'Short',
            'coat_texture' => 'Wavy',
            'undercoat' => 'Dense',
            'hairless' => null,
            'feather_type' => null,
            'mane_type' => null,
            'seasonal_shedding' => 'Heavy',
        ])
        ->and(serialize($presented))->not->toContain('flaky');

    $viewer = User::factory()->create();
    Livewire::actingAs($viewer)
        ->test(PublicPetProfile::class, ['petProfile' => $profile])
        ->assertSee(__('pet_profiles.body_covering.public_title'))
        ->assertSee('Short')
        ->assertSee('Wavy')
        ->assertDontSee('Private flaky patch.');
});

it('treats private-only and invalid legacy body covering values safely', function (): void {
    $owner = User::factory()->create();
    $privateOnly = bodyCoveringProfile($owner, [
        'profile_key' => 'pet-private-covering',
        'slug' => 'pet-private-covering',
        'profile_data' => ['body_covering' => ['skin_condition' => 'Private note.']],
    ]);
    $invalid = bodyCoveringProfile($owner, [
        'profile_key' => 'pet-invalid-covering',
        'slug' => 'pet-invalid-covering',
        'profile_data' => [
            'body_covering' => [
                'coat_length' => 'forged',
                'coat_texture' => ['not-a-string'],
                'hairless' => 'yes',
            ],
        ],
    ]);

    expect(app(PetBodyCoveringPresenter::class)->for($privateOnly))->toBeNull()
        ->and(app(PetBodyCoveringPresenter::class)->for($invalid))->toBeNull();
});

it('counts structured body covering as appearance completion', function (): void {
    $owner = User::factory()->create();
    $profile = bodyCoveringProfile($owner, [
        'profile_data' => [
            'body_covering' => [
                'schema_version' => 1,
                'coat_length' => null,
                'coat_texture' => null,
                'undercoat' => null,
                'hairless' => false,
                'feather_type' => null,
                'skin_condition' => 'Manager-only skin description.',
                'mane_type' => null,
                'seasonal_shedding' => null,
            ],
        ],
    ]);

    $appearance = collect(app(PetProfileCompletionPresenter::class)->present(
        $profile,
        PetProfileCompletionStep::Appearance,
    ))->firstWhere('value', PetProfileCompletionStep::Appearance->value);

    expect($appearance['complete'])->toBeTrue();
});
