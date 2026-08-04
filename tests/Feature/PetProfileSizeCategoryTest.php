<?php

declare(strict_types=1);

use App\Actions\UpdatePetProfileStep;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileCompletionStep;
use App\Enums\PetSizeCategory;
use App\Livewire\Pets\ManagePetProfile;
use App\Livewire\Pets\PublicPetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\User;
use App\Services\PetProfileCompletionPresenter;
use App\Services\PetSizeCategoryPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function sizeCategoryProfile(User $owner, array $attributes = []): PetProfile
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

it('adds a nullable indexed enum-backed size category without inventing a default', function (): void {
    $owner = User::factory()->create();
    $profile = sizeCategoryProfile($owner);
    $indexes = collect(Schema::getIndexes('pet_profiles'));

    expect(Schema::hasColumn('pet_profiles', 'size_category'))->toBeTrue()
        ->and($profile->size_category)->toBeNull()
        ->and($indexes->contains(
            fn (array $index): bool => $index['name'] === 'pet_profiles_size_status_idx'
                && $index['columns'] === ['size_category', 'status', 'id'],
        ))->toBeTrue();
});

it('defines every requested localized size category', function (string $value): void {
    $category = PetSizeCategory::from($value);

    expect($category->label())->not->toBe('')
        ->and($category->description())->not->toBe('')
        ->and($category->label())->not->toContain('pet_profiles.size');
})->with([
    'very-small',
    'small',
    'medium',
    'large',
    'very-large',
    'individual',
    'not-applicable',
]);

it('stores and clears size through the authorized appearance action', function (): void {
    $owner = User::factory()->create();
    $profile = sizeCategoryProfile($owner);
    $this->actingAs($owner);
    $action = app(UpdatePetProfileStep::class);

    $updated = $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: ['size_category' => PetSizeCategory::Medium->value],
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'size-category-store-001',
    );

    expect($updated->size_category)->toBe(PetSizeCategory::Medium)
        ->and(data_get($updated->profile_data, 'size_category'))->toBeNull()
        ->and(PetProfileLifecycleEvent::query()
            ->where('pet_profile_id', $profile->id)
            ->where('event_type', 'profile-step-updated')
            ->count())->toBe(1);

    $cleared = $action->handle(
        profile: $updated,
        step: PetProfileCompletionStep::Appearance,
        data: ['size_category' => ''],
        expectedLockVersion: $updated->lock_version,
        idempotencyKey: 'size-category-clear-001',
    );

    expect($cleared->size_category)->toBeNull();
});

it('rejects forged size values at the action boundary', function (mixed $value): void {
    $owner = User::factory()->create();
    $profile = sizeCategoryProfile($owner);
    $this->actingAs($owner);

    expect(fn (): PetProfile => app(UpdatePetProfileStep::class)->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: ['size_category' => $value],
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'size-category-invalid-'.md5(serialize($value)),
    ))->toThrow(ValidationException::class);

    expect($profile->refresh()->size_category)->toBeNull()
        ->and($profile->lock_version)->toBe(1);
})->with([
    'unknown category' => 'extra-colossal',
    'array payload' => [['medium']],
    'boolean payload' => true,
]);

it('preserves omitted size and keeps replayed or unchanged saves mutation-free', function (): void {
    $owner = User::factory()->create();
    $profile = sizeCategoryProfile($owner, ['size_category' => PetSizeCategory::Small]);
    $this->actingAs($owner);
    $action = app(UpdatePetProfileStep::class);

    $unchanged = $action->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: [],
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'size-category-omitted-001',
    );
    $same = $action->handle(
        profile: $unchanged,
        step: PetProfileCompletionStep::Appearance,
        data: ['size_category' => PetSizeCategory::Small->value],
        expectedLockVersion: $unchanged->lock_version,
        idempotencyKey: 'size-category-same-001',
    );
    $first = $action->handle(
        profile: $same,
        step: PetProfileCompletionStep::Appearance,
        data: ['size_category' => PetSizeCategory::Large->value],
        expectedLockVersion: $same->lock_version,
        idempotencyKey: 'size-category-replay-001',
    );
    $replayed = $action->handle(
        profile: $same,
        step: PetProfileCompletionStep::Appearance,
        data: ['size_category' => PetSizeCategory::VeryLarge->value],
        expectedLockVersion: $same->lock_version,
        idempotencyKey: 'size-category-replay-001',
    );

    expect($unchanged->lock_version)->toBe(1)
        ->and($same->lock_version)->toBe(1)
        ->and($first->lock_version)->toBe(2)
        ->and($replayed->lock_version)->toBe(2)
        ->and($replayed->size_category)->toBe(PetSizeCategory::Large);
});

it('refuses size mutations for an unmanaged profile', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $profile = sizeCategoryProfile($owner);
    $this->actingAs($outsider);

    expect(fn (): PetProfile => app(UpdatePetProfileStep::class)->handle(
        profile: $profile,
        step: PetProfileCompletionStep::Appearance,
        data: ['size_category' => PetSizeCategory::Medium->value],
        expectedLockVersion: $profile->lock_version,
        idempotencyKey: 'size-category-denied-001',
    ))->toThrow(ValidationException::class);
});

it('renders saves and restores the localized accessible size editor', function (string $locale): void {
    $owner = User::factory()->create(['locale' => $locale]);
    $profile = sizeCategoryProfile($owner);
    app()->setLocale($locale);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSeeHtml('id="managed-pet-size-category"')
        ->assertSee(__('pet_profiles.size.title'))
        ->assertSee(PetSizeCategory::Individual->label())
        ->assertDontSee('pet_profiles.size')
        ->set('form.sizeCategory', PetSizeCategory::VerySmall->value)
        ->call('saveAppearance')
        ->assertHasNoErrors()
        ->assertSet('feedback', __('pet_profiles.feedback.appearance_saved'));

    $profile->refresh();

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::Appearance->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSet('form.sizeCategory', PetSizeCategory::VerySmall->value);
})->with(['en', 'lt', 'ru']);

it('projects a localized size fact publicly without adding queries', function (string $locale): void {
    $owner = User::factory()->create(['locale' => $locale]);
    $profile = sizeCategoryProfile($owner, [
        'size_category' => PetSizeCategory::Individual,
    ]);
    app()->setLocale($locale);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $presented = app(PetSizeCategoryPresenter::class)->for($profile);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($queries)->toBe([])
        ->and($presented)->toBe([
            'value' => PetSizeCategory::Individual->value,
            'label' => PetSizeCategory::Individual->label(),
            'description' => PetSizeCategory::Individual->description(),
        ]);

    Livewire::actingAs($owner)
        ->test(PublicPetProfile::class, ['petProfile' => $profile])
        ->assertSee(__('pet_profiles.size.public_title'))
        ->assertSee(PetSizeCategory::Individual->label())
        ->assertSee(PetSizeCategory::Individual->description())
        ->assertDontSee('pet_profiles.size');
})->with(['en', 'lt', 'ru']);

it('renders the real appearance route with strict missing-attribute protection enabled', function (): void {
    $owner = User::factory()->create();
    $profile = sizeCategoryProfile($owner);
    $this->actingAs($owner);

    Model::preventAccessingMissingAttributes();

    try {
        $this->get(route('pets.manage.show', [
            'petProfile' => $profile->profile_key,
            'step' => PetProfileCompletionStep::Appearance->value,
        ]))
            ->assertOk()
            ->assertSeeHtml('id="managed-pet-size-category"');
    } finally {
        Model::preventAccessingMissingAttributes(false);
    }
});

it('counts a recorded size category as appearance completion', function (): void {
    $owner = User::factory()->create();
    $profile = sizeCategoryProfile($owner, [
        'size_category' => PetSizeCategory::NotApplicable,
    ]);

    $appearance = collect(app(PetProfileCompletionPresenter::class)->present(
        $profile,
        PetProfileCompletionStep::Appearance,
    ))->firstWhere('value', PetProfileCompletionStep::Appearance->value);

    expect($appearance['complete'])->toBeTrue();
});
