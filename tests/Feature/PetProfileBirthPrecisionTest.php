<?php

declare(strict_types=1);

use App\Actions\UpdatePetProfileStep;
use App\Enums\PetBirthDatePrecision;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileCompletionStep;
use App\Livewire\Pets\ManagePetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\User;
use App\Services\LocaleFormatter;
use App\Services\PetBirthDetailsNormalizer;
use App\Services\PetProfileAgeCalculator;
use App\Services\PetProfileAgeLabel;
use App\Services\SearchPresenter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function birthPrecisionProfile(User $owner, array $attributes = []): PetProfile
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

it('stores typed birth precision and estimate metadata on pet profiles', function (): void {
    $columns = Schema::getColumnListing('pet_profiles');
    $profile = PetProfile::factory()->create([
        'birth_date' => null,
        'birth_date_precision' => PetBirthDatePrecision::AgeEstimate,
        'estimated_age_months' => 28,
        'estimated_age_recorded_at' => '2026-08-03 10:00:00',
        'birthday_celebration_month' => 8,
        'birthday_celebration_day' => 3,
    ]);

    expect($columns)->toContain(
        'estimated_age_months',
        'estimated_age_recorded_at',
        'birthday_celebration_month',
        'birthday_celebration_day',
    )
        ->and($profile->birth_date_precision)->toBe(PetBirthDatePrecision::AgeEstimate)
        ->and($profile->estimated_age_months)->toBe(28)
        ->and($profile->estimated_age_recorded_at?->toDateTimeString())->toBe('2026-08-03 10:00:00')
        ->and($profile->birthday_celebration_month)->toBe(8)
        ->and($profile->birthday_celebration_day)->toBe(3);
});

it('normalizes every supported birth-information mode through the Livewire workspace', function (
    string $precision,
    array $formValues,
    ?string $birthDate,
    ?int $estimatedMonths,
): void {
    $this->travelTo('2026-08-03 10:00:00');
    $owner = User::factory()->create();
    $profile = birthPrecisionProfile($owner, [
        'birth_date' => null,
        'birth_date_precision' => PetBirthDatePrecision::Unknown,
    ]);
    $component = Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::AgeAndSex->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('form.birthDatePrecision', $precision);

    foreach ($formValues as $property => $value) {
        $component->set("form.{$property}", $value);
    }

    $component->call('saveAgeAndSex')->assertHasNoErrors();
    $profile->refresh();

    expect($profile->birth_date_precision->value)->toBe($precision)
        ->and($profile->birth_date?->toDateString())->toBe($birthDate)
        ->and($profile->estimated_age_months)->toBe($estimatedMonths);

    if ($precision === PetBirthDatePrecision::AgeEstimate->value) {
        expect($profile->estimated_age_recorded_at?->toDateTimeString())
            ->toBe('2026-08-03 10:00:00');
    } else {
        expect($profile->estimated_age_recorded_at)->toBeNull();
    }
})->with([
    'exact date' => ['exact', ['birthDate' => '2020-05-14'], '2020-05-14', null],
    'estimated date' => ['estimated', ['birthDate' => '2020-05-14'], '2020-05-14', null],
    'month and year' => ['month', ['birthMonth' => '2020-05'], '2020-05-01', null],
    'year only' => ['year', ['birthYear' => '2020'], '2020-01-01', null],
    'estimated age' => ['age-estimate', [
        'estimatedAgeYears' => '3',
        'estimatedAgeMonths' => '4',
    ], null, 40],
    'unknown' => ['unknown', [], null, null],
]);

it('keeps an optional celebration day separate from the known birth value', function (): void {
    $owner = User::factory()->create();
    $profile = birthPrecisionProfile($owner);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::AgeAndSex->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('form.birthDatePrecision', 'year')
        ->set('form.birthYear', '2020')
        ->set('form.celebrationMonth', '2')
        ->set('form.celebrationDay', '29')
        ->call('saveAgeAndSex')
        ->assertHasNoErrors();

    $profile->refresh();

    expect($profile->birth_date?->toDateString())->toBe('2020-01-01')
        ->and($profile->birthday_celebration_month)->toBe(2)
        ->and($profile->birthday_celebration_day)->toBe(29);

    Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::AgeAndSex->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->set('form.birthDatePrecision', 'exact')
        ->set('form.birthDate', '2020-02-28')
        ->call('saveAgeAndSex')
        ->assertHasNoErrors();

    expect($profile->refresh()->birthday_celebration_month)->toBeNull()
        ->and($profile->birthday_celebration_day)->toBeNull();
});

it('rejects missing future and internally inconsistent birth details at the action boundary', function (): void {
    $this->travelTo('2026-08-03 10:00:00');
    $owner = User::factory()->create();
    $profile = birthPrecisionProfile($owner);
    $this->actingAs($owner);
    $action = app(UpdatePetProfileStep::class);

    expect(fn (): PetProfile => $action->handle(
        $profile,
        PetProfileCompletionStep::AgeAndSex,
        [
            'birth_date_precision' => 'estimated',
            'sex' => 'unknown',
            'reproductive_status' => 'unknown',
        ],
        $profile->lock_version,
        'birth-invalid-missing-date',
    ))->toThrow(ValidationException::class);

    expect(fn (): array => app(PetBirthDetailsNormalizer::class)->normalize([
        'birth_date_precision' => 'month',
        'birth_month' => '2026-09',
    ]))->toThrow(ValidationException::class);

    expect(fn (): array => app(PetBirthDetailsNormalizer::class)->normalize([
        'birth_date_precision' => 'age-estimate',
        'estimated_age_years' => PetBirthDetailsNormalizer::MAX_AGE_YEARS,
        'estimated_age_month_remainder' => 1,
    ]))->toThrow(ValidationException::class);

    expect(fn (): array => app(PetBirthDetailsNormalizer::class)->normalize([
        'birth_date_precision' => 'unknown',
        'birthday_celebration_month' => 2,
        'birthday_celebration_day' => 30,
    ]))->toThrow(ValidationException::class);
});

it('advances estimated age without resetting its original observation time', function (): void {
    $this->travelTo('2025-01-01 09:00:00');
    $owner = User::factory()->create();
    $profile = birthPrecisionProfile($owner);
    $this->actingAs($owner);
    $action = app(UpdatePetProfileStep::class);
    $profile = $action->handle(
        $profile,
        PetProfileCompletionStep::AgeAndSex,
        [
            'birth_date_precision' => 'age-estimate',
            'estimated_age_months' => 24,
            'sex' => 'unknown',
            'reproductive_status' => 'unknown',
        ],
        $profile->lock_version,
        'birth-estimate-first-observation',
    );
    $recordedAt = $profile->estimated_age_recorded_at?->toDateTimeString();

    $this->travelTo('2026-02-01 09:00:00');
    $profile = $action->handle(
        $profile,
        PetProfileCompletionStep::AgeAndSex,
        [
            'birth_date_precision' => 'age-estimate',
            'estimated_age_months' => 24,
            'sex' => 'unknown',
            'reproductive_status' => 'unknown',
        ],
        $profile->lock_version,
        'birth-estimate-same-observation',
    );
    $range = app(PetProfileAgeCalculator::class)->monthsRange($profile);

    expect($profile->estimated_age_recorded_at?->toDateTimeString())->toBe($recordedAt)
        ->and($range)->toBe(['minimum' => 37, 'maximum' => 37])
        ->and(app(PetProfileAgeLabel::class)->for($profile))
        ->toBe(__('pet_profiles.public.approximately_age', [
            'age' => app(LocaleFormatter::class)->list([
                trans_choice('pet_profiles.public.age_years', 3, ['count' => '3']),
                trans_choice('pet_profiles.public.age_months', 1, ['count' => '1']),
            ]),
        ]));
});

it('renders honest ranges and celebration days in the public profile for every locale', function (string $locale): void {
    $this->travelTo('2026-08-03 10:00:00');
    $owner = User::factory()->create(['locale' => $locale]);
    $profile = birthPrecisionProfile($owner, [
        'name' => 'Baks',
        'birth_date' => '2020-01-01',
        'birth_date_precision' => PetBirthDatePrecision::Year,
        'birthday_celebration_month' => 8,
        'birthday_celebration_day' => 3,
    ]);
    $this->actingAs($owner);
    app()->setLocale($locale);
    $age = app(PetProfileAgeLabel::class);
    $expectedAge = $age->for($profile);
    $expectedCelebration = $age->celebrationFor($profile);

    expect(app(PetProfileAgeCalculator::class)->monthsRange($profile))
        ->toBe(['minimum' => 67, 'maximum' => 79])
        ->and($expectedAge)->not->toBeNull()
        ->and($expectedCelebration)->not->toBeNull();

    $this->get(route('pets.profile', ['petProfile' => $profile->profile_key]))
        ->assertOk()
        ->assertSee((string) $expectedAge)
        ->assertSee((string) $expectedCelebration)
        ->assertDontSee('pet_profiles.');
})->with(['en', 'lt', 'ru']);

it('shows only the inputs belonging to the selected birth-information mode', function (): void {
    $owner = User::factory()->create();
    $profile = birthPrecisionProfile($owner);
    $component = Livewire::actingAs($owner)
        ->withQueryParams(['step' => PetProfileCompletionStep::AgeAndSex->value])
        ->test(ManagePetProfile::class, ['petProfile' => $profile])
        ->assertSeeHtml('id="managed-pet-birth-date"')
        ->assertDontSeeHtml('id="managed-pet-birth-year"')
        ->set('form.birthDatePrecision', 'year')
        ->assertSeeHtml('id="managed-pet-birth-year"')
        ->assertDontSeeHtml('id="managed-pet-birth-date"')
        ->set('form.birthDatePrecision', 'age-estimate')
        ->assertSeeHtml('id="managed-pet-estimated-age-years"')
        ->assertSeeHtml('id="managed-pet-estimated-age-months"')
        ->set('form.birthDatePrecision', 'unknown')
        ->assertSee(__('pet_profiles.completion.help.age_unknown'));

    $component->assertSeeHtml('id="managed-pet-celebration-month"');
});

it('uses the shared age projection in the lost and found pet defaults', function (): void {
    $this->travelTo('2026-08-03 10:00:00');
    $owner = User::factory()->create();
    $profile = birthPrecisionProfile($owner, [
        'birth_date' => null,
        'birth_date_precision' => PetBirthDatePrecision::AgeEstimate,
        'estimated_age_months' => 30,
        'estimated_age_recorded_at' => now(),
    ]);
    $this->actingAs($owner);
    $editor = app(SearchPresenter::class)->editor($profile->profile_key);

    expect(data_get($editor, 'default_pet.age'))
        ->toBe(app(PetProfileAgeLabel::class)->for($profile));
});
