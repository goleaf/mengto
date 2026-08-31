<?php

declare(strict_types=1);

use App\Actions\CompleteOnboardingPreferences;
use App\Actions\UpdateProfilePreferences;
use App\Enums\OnboardingStep;
use App\Enums\UserStatus;
use App\Livewire\Auth\Login;
use App\Livewire\Onboarding;
use App\Livewire\ProfileSettings;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Validation\ProfilePreferenceRules;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('preferences hydrate from the account and expose accessible configured controls', function (string $locale): void {
    $this->authenticatedUser->forceFill([
        'locale' => $locale,
        'timezone' => 'Europe/Vilnius',
    ])->saveOrFail();
    UserOnboarding::factory()->for($this->authenticatedUser)->preferences()->create();
    session()->put('locale', $locale);

    Livewire::test(Onboarding::class)
        ->assertSet('preferencesForm.locale', $locale)
        ->assertSet('preferencesForm.timezone', 'Europe/Vilnius');

    $response = $this->get(route('onboarding.show'))->assertOk();
    $html = (string) $response->getContent();
    $xpath = responseXPath($response);

    expect($xpath->query('//h1'))->toHaveCount(1)
        ->and($xpath->query('//form[@novalidate]'))->toHaveCount(1)
        ->and($xpath->query('//label[@for="onboarding-locale"]'))->toHaveCount(1)
        ->and($xpath->query('//select[@id="onboarding-locale" and @required and @aria-required="true"]'))->toHaveCount(1)
        ->and($xpath->query('//label[@for="onboarding-timezone"]'))->toHaveCount(1)
        ->and($xpath->query('//select[@id="onboarding-timezone" and @required and @aria-required="true"]'))->toHaveCount(1)
        ->and($xpath->query('//*[@id="onboarding-locale-help"]'))->toHaveCount(1)
        ->and($xpath->query('//*[@id="onboarding-timezone-help"]'))->toHaveCount(1)
        ->and($html)->toContain('wire:submit="savePreferences"')
        ->toContain('wire:target="savePreferences"')
        ->toContain('wire:offline')
        ->toContain('wire:dirty')
        ->not->toContain('auth.profile.')
        ->not->toContain('onboarding.');
})->with(['en', 'lt', 'ru']);

test('preference validation has one neutral canonical rule source', function (): void {
    $form = File::get(app_path('Livewire/Forms/ProfilePreferencesForm.php'));
    $action = File::get(app_path('Actions/UpdateProfilePreferences.php'));

    expect(ProfilePreferenceRules::rules())
        ->toHaveKeys(['locale', 'timezone'])
        ->and($form)->toContain('ProfilePreferenceRules::rules()')
        ->and($action)->toContain('ProfilePreferenceRules::rules()')
        ->and($form)->not->toContain("__('onboarding.validation.locale')")
        ->and($form)->not->toContain("__('onboarding.validation.timezone')");
});

test('saving preferences immediately applies the selected locale and renders the next step', function (
    string $locale,
    string $timezone,
): void {
    UserOnboarding::factory()->for($this->authenticatedUser)->preferences()->create();
    app()->setLocale('en');
    session()->put('locale', 'en');

    Livewire::test(Onboarding::class)
        ->set('preferencesForm.locale', $locale)
        ->set('preferencesForm.timezone', $timezone)
        ->call('savePreferences')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.show'));

    expect($this->authenticatedUser->fresh())
        ->locale->toBe($locale)
        ->timezone->toBe($timezone)
        ->and(session('locale'))->toBe($locale)
        ->and(app()->getLocale())->toBe($locale)
        ->and($this->authenticatedUser->onboarding()->firstOrFail()->current_step)
        ->toBe(OnboardingStep::PetRelationship);

    app()->setLocale($locale);
    $this->get(route('onboarding.show'))
        ->assertOk()
        ->assertSee('lang="'.$locale.'"', false)
        ->assertSee(__('onboarding.steps.pet_relationship.title'));
})->with([
    'English and UTC' => ['en', 'UTC'],
    'Lithuanian and Vilnius' => ['lt', 'Europe/Vilnius'],
    'Russian and London' => ['ru', 'Europe/London'],
    'English and New York' => ['en', 'America/New_York'],
    'Lithuanian and Tokyo' => ['lt', 'Asia/Tokyo'],
]);

test('unsupported locale and timezone input cannot change preferences or progress', function (
    string $locale,
    string $timezone,
): void {
    $state = UserOnboarding::factory()->for($this->authenticatedUser)->preferences()->create();

    Livewire::test(Onboarding::class)
        ->set('preferencesForm.locale', $locale)
        ->set('preferencesForm.timezone', $timezone)
        ->call('savePreferences')
        ->assertHasErrors(['preferencesForm.locale', 'preferencesForm.timezone']);

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('en')
        ->timezone->toBe('Europe/Vilnius')
        ->and($state->fresh())
        ->current_step->toBe(OnboardingStep::Preferences)
        ->preferences_completed_at->toBeNull()
        ->lock_version->toBe(2);
})->with([
    'path and nonexistent zone' => ['../../ru', 'Europe/NotReal'],
    'script and offset' => ['<script>', 'GMT+3'],
    'unsupported regional locale and city' => ['en-US', 'Vilnius'],
    'case altered values' => ['RU', 'europe/vilnius'],
    'whitespace altered values' => [' ru ', ' Europe/Vilnius '],
]);

test('direct preference actions reject unsupported canonical values', function (
    string $locale,
    string $timezone,
): void {
    expect(fn () => app(UpdateProfilePreferences::class)->handle(
        $this->authenticatedUser,
        ['locale' => $locale, 'timezone' => $timezone],
    ))->toThrow(ValidationException::class);

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('en')
        ->timezone->toBe('Europe/Vilnius');
})->with([
    'path traversal and invented zone' => ['../../ru', 'Europe/NotReal'],
    'unsupported locale and offset' => ['de', 'GMT+3'],
]);

test('two pre-mounted preference submissions are an idempotent replay', function (): void {
    $state = UserOnboarding::factory()->for($this->authenticatedUser)->preferences()->create();
    $first = Livewire::test(Onboarding::class)
        ->set('preferencesForm.locale', 'lt')
        ->set('preferencesForm.timezone', 'Europe/Vilnius');
    $second = Livewire::test(Onboarding::class)
        ->set('preferencesForm.locale', 'lt')
        ->set('preferencesForm.timezone', 'Europe/Vilnius');

    $first
        ->call('savePreferences')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.show'));
    $persisted = $state->fresh();

    $second
        ->call('savePreferences')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.show'));

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('lt')
        ->timezone->toBe('Europe/Vilnius')
        ->and($state->fresh())
        ->current_step->toBe(OnboardingStep::PetRelationship)
        ->preferences_completed_at?->equalTo($persisted?->preferences_completed_at)->toBeTrue()
        ->lock_version->toBe(3);
});

test('malformed exact replays fail as validation conflicts without corrupting persisted state', function (array $payload): void {
    $state = UserOnboarding::factory()->for($this->authenticatedUser)->preferences()->create();
    $action = app(CompleteOnboardingPreferences::class);
    $action->handle(
        $this->authenticatedUser,
        ['locale' => 'lt', 'timezone' => 'Europe/Vilnius'],
        OnboardingStep::Preferences,
        $state->lock_version,
    );
    $expected = $state->fresh();

    expect(fn () => $action->handle(
        $this->authenticatedUser,
        $payload,
        OnboardingStep::Preferences,
        2,
    ))->toThrow(ValidationException::class);

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('lt')
        ->timezone->toBe('Europe/Vilnius')
        ->and($state->fresh())
        ->current_step->toBe(OnboardingStep::PetRelationship)
        ->preferences_completed_at?->equalTo($expected?->preferences_completed_at)->toBeTrue()
        ->lock_version->toBe(3);
})->with([
    'empty' => [[]],
    'locale only' => [['locale' => 'lt']],
    'timezone only' => [['timezone' => 'Europe/Vilnius']],
    'array values' => [['locale' => ['lt'], 'timezone' => ['UTC']]],
    'null values' => [['locale' => null, 'timezone' => null]],
]);

test('stale inactive status denies every canonical preference mutation', function (
    UserStatus $status,
    string $operation,
): void {
    $state = UserOnboarding::factory()->for($this->authenticatedUser)->preferences()->create();
    $staleUser = $this->authenticatedUser;
    DB::table('users')->where('id', $staleUser->id)->update(['status' => $status->value]);

    $mutation = $operation === 'profile'
        ? fn () => app(UpdateProfilePreferences::class)->handle($staleUser, [
            'locale' => 'ru',
            'timezone' => 'Europe/London',
        ])
        : fn () => app(CompleteOnboardingPreferences::class)->handle(
            $staleUser,
            ['locale' => 'ru', 'timezone' => 'Europe/London'],
            OnboardingStep::Preferences,
            $state->lock_version,
        );

    expect($mutation)->toThrow(HttpException::class);

    expect($staleUser->fresh())
        ->locale->toBe('en')
        ->timezone->toBe('Europe/Vilnius')
        ->status->toBe($status)
        ->and($state->fresh())
        ->current_step->toBe(OnboardingStep::Preferences)
        ->preferences_completed_at->toBeNull()
        ->lock_version->toBe(2);
})->with([
    'blocked profile update' => [UserStatus::Blocked, 'profile'],
    'suspended profile update' => [UserStatus::Suspended, 'profile'],
    'blocked onboarding update' => [UserStatus::Blocked, 'onboarding'],
    'suspended onboarding update' => [UserStatus::Suspended, 'onboarding'],
]);

test('direct profile preference updates authorize the account and discard unrelated fields', function (): void {
    $other = User::factory()->create();

    expect(fn () => app(UpdateProfilePreferences::class)->handle($other, [
        'locale' => 'lt',
        'timezone' => 'UTC',
    ]))->toThrow(AuthorizationException::class);

    app(UpdateProfilePreferences::class)->handle($this->authenticatedUser, [
        'locale' => 'lt',
        'timezone' => 'UTC',
        'user_id' => $other->id,
        'actor_key' => 'forged-actor',
        'status' => UserStatus::Blocked->value,
        'is_admin' => true,
    ]);

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('lt')
        ->timezone->toBe('UTC')
        ->actor_key->toBe('test-member')
        ->status->toBe(UserStatus::Active)
        ->is_admin->toBeFalse();
});

test('a signed profile settings snapshot cannot mutate a different authenticated account', function (): void {
    $owner = User::factory()->onboarded()->create([
        'locale' => 'en',
        'timezone' => 'UTC',
    ]);
    $component = Livewire::actingAs($owner)
        ->test(ProfileSettings::class)
        ->set('form.locale', 'ru')
        ->set('form.timezone', 'Europe/Riga');

    $other = User::factory()->onboarded()->create([
        'locale' => 'lt',
        'timezone' => 'Europe/Vilnius',
    ]);
    $this->actingAs($other);
    Livewire::actingAs($other);

    $component->call('save')->assertForbidden();

    expect($other->fresh())
        ->locale->toBe('lt')
        ->timezone->toBe('Europe/Vilnius');
});

test('saved Lithuanian preferences survive logout and login at the persisted pet step', function (): void {
    $user = User::factory()->onboardingAtPreferences()->create([
        'password' => 'Secure-Paw-2026',
        'locale' => 'en',
        'timezone' => 'UTC',
    ]);
    $this->actingAs($user);
    Livewire::actingAs($user);

    Livewire::test(Onboarding::class)
        ->set('preferencesForm.locale', 'lt')
        ->set('preferencesForm.timezone', 'Europe/Vilnius')
        ->call('savePreferences')
        ->assertRedirect(route('onboarding.show'));

    $this->post(route('logout'))->assertRedirect(route('login'));

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'Secure-Paw-2026')
        ->call('authenticate')
        ->assertRedirect(route('onboarding.show'));

    app()->setLocale('lt');
    expect(session('locale'))->toBe('lt');
    $this->get(route('onboarding.show'))
        ->assertOk()
        ->assertSee('lang="lt"', false)
        ->assertSee(__('onboarding.steps.pet_relationship.title'));
});

test('profile settings use persisted onboarding preferences without resetting completion', function (): void {
    $user = User::factory()->onboarded()->create([
        'locale' => 'ru',
        'timezone' => 'Europe/Vilnius',
    ]);
    $completedAt = $user->onboarding()->firstOrFail()->completed_at;
    $this->actingAs($user);
    Livewire::actingAs($user);

    Livewire::test(ProfileSettings::class)
        ->assertSet('form.locale', 'ru')
        ->assertSet('form.timezone', 'Europe/Vilnius')
        ->set('form.locale', 'en')
        ->set('form.timezone', 'America/New_York')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh())
        ->locale->toBe('en')
        ->timezone->toBe('America/New_York')
        ->and($user->onboarding()->firstOrFail())
        ->current_step->toBe(OnboardingStep::Complete)
        ->completed_at?->equalTo($completedAt)->toBeTrue();
});
