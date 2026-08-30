<?php

declare(strict_types=1);

use App\Actions\DeferOnboardingPetRelationship;
use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Livewire\Onboarding;
use App\Livewire\Pets\CreatePetProfile;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\SocialActorResolver;
use Database\Seeders\OnboardingBrowserSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('the wizard route preserves lifecycle precedence', function (): void {
    auth()->logout();
    $this->get(route('onboarding.show'))->assertRedirect(route('login'));

    config()->set('platform.email_verification_enabled', true);
    $unverified = User::factory()->unverified()->onboardingIncomplete()->create();
    $this->actingAs($unverified);
    $this->get(route('onboarding.show'))->assertRedirect(route('verification.notice'));

    $completed = User::factory()->onboarded()->create();
    $this->actingAs($completed);
    $this->get(route('onboarding.show'))->assertRedirect(route('home'));
});

test('every persisted state renders only its canonical screen and semantic progress', function (
    string $factoryState,
    OnboardingStep $currentStep,
    string $currentTitleKey,
    array $hiddenTitleKeys,
): void {
    $factory = UserOnboarding::factory()->for($this->authenticatedUser);
    $state = $factoryState === 'introduction'
        ? $factory->create()
        : $factory->{$factoryState}()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);

    $response = $this->get(route('onboarding.show'))->assertOk();
    $html = (string) $response->getContent();
    $xpath = responseXPath($response);

    expect($xpath->query('//main'))->toHaveCount(1)
        ->and($xpath->query('//h1'))->toHaveCount(1)
        ->and($xpath->query('//ol[@data-onboarding-progress-list]'))->toHaveCount(1)
        ->and($xpath->query('//li[@data-onboarding-step]'))->toHaveCount(4)
        ->and($xpath->query('//li[@aria-current="step" and @data-step="'.$currentStep->value.'"]'))->toHaveCount(1)
        ->and($xpath->query('//li[@data-status="complete"]'))->toHaveCount($currentStep->position() - 1)
        ->and($html)->toContain(__('onboarding.'.$currentTitleKey));

    if ($currentStep->position() > 1) {
        expect($html)->toContain(__('onboarding.progress.status.complete'));
    }

    foreach ($hiddenTitleKeys as $hiddenTitleKey) {
        expect($html)->not->toContain(__('onboarding.'.$hiddenTitleKey));
    }

    expect($state->fresh()?->current_step)->toBe($currentStep);
})->with([
    'introduction' => ['introduction', OnboardingStep::Introduction, 'steps.introduction.title', [
        'steps.preferences.title',
        'steps.pet_relationship.title',
        'steps.privacy_discovery.title',
    ]],
    'preferences' => ['preferences', OnboardingStep::Preferences, 'steps.preferences.title', [
        'steps.introduction.title',
        'steps.pet_relationship.title',
        'steps.privacy_discovery.title',
    ]],
    'pet relationship' => ['petRelationship', OnboardingStep::PetRelationship, 'steps.pet_relationship.title', [
        'steps.introduction.title',
        'steps.preferences.title',
        'steps.privacy_discovery.title',
    ]],
    'privacy' => ['privacyDiscovery', OnboardingStep::PrivacyDiscovery, 'steps.privacy_discovery.title', [
        'steps.introduction.title',
        'steps.preferences.title',
        'steps.pet_relationship.title',
    ]],
]);

test('introduction advances from the explicit action without a meaningless checkbox and is replay safe', function (): void {
    $state = UserOnboarding::factory()->for($this->authenticatedUser)->create();

    $first = Livewire::test(Onboarding::class)
        ->assertDontSeeHtml('wire:model="introductionAcknowledged"');
    $replayedSnapshot = Livewire::test(Onboarding::class);

    $first
        ->call('acknowledgeIntroduction')
        ->assertHasNoErrors();

    $completedAt = $state->fresh()?->introduction_completed_at;

    $replayedSnapshot->call('acknowledgeIntroduction')->assertHasNoErrors();

    expect($state->fresh())
        ->current_step->toBe(OnboardingStep::Preferences)
        ->introduction_completed_at?->equalTo($completedAt)->toBeTrue()
        ->lock_version->toBe(2);
});

test('pet and privacy choices use labelled semantic groups with mutation feedback', function (string $stateName): void {
    UserOnboarding::factory()->for($this->authenticatedUser)->{$stateName}()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);

    $response = $this->get(route('onboarding.show'))->assertOk();
    $xpath = responseXPath($response);
    $html = (string) $response->getContent();

    expect($xpath->query('//fieldset[@data-onboarding-choice-group]/legend'))->toHaveCount(1)
        ->and($html)->toContain('wire:loading.attr="disabled"')
        ->and($html)->toContain('wire:loading.attr="aria-busy"')
        ->and($html)->toContain('role="status"')
        ->and($html)->toContain('aria-live="polite"');
})->with([
    'pet relationship' => ['petRelationship'],
    'privacy' => ['privacyDiscovery'],
]);

test('progress reports completed work without exposing a false navigation landmark', function (): void {
    UserOnboarding::factory()->for($this->authenticatedUser)->privacyDiscovery()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);

    $response = $this->get(route('onboarding.show'))->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->query('//*[@data-onboarding-progress]'))->toHaveCount(1)
        ->and($xpath->query('//nav[@data-onboarding-progress]'))->toHaveCount(0)
        ->and($xpath->query('//progress[@value="3" and @max="4"]'))->toHaveCount(1);
});

test('required pet and privacy input reaches localized Livewire validation semantics', function (): void {
    UserOnboarding::factory()->for($this->authenticatedUser)->petRelationship()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);

    Livewire::test(Onboarding::class)
        ->call('savePetRelationship')
        ->assertHasErrors(['petForm.choice'])
        ->assertSeeHtml('wire:submit="savePetRelationship"')
        ->assertSeeHtml('novalidate')
        ->assertSeeHtml('aria-required="true"')
        ->assertSeeHtml('aria-invalid="true"')
        ->assertSeeHtml('required');

    $this->authenticatedUser->onboarding()->firstOrFail()->forceFill([
        'current_step' => OnboardingStep::PrivacyDiscovery,
        'pet_relationship_choice' => OnboardingPetChoice::NotNow,
        'pet_relationship_completed_at' => now(),
        'lock_version' => 4,
    ])->saveOrFail();

    $response = $this->get(route('onboarding.show'))->assertOk();

    expect((string) $response->getContent())
        ->toContain('wire:submit="savePrivacy"')
        ->toContain('novalidate');
});

test('preference redirect asks the fresh localized document to focus its step heading', function (): void {
    UserOnboarding::factory()->for($this->authenticatedUser)->preferences()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);

    Livewire::test(Onboarding::class)
        ->set('preferencesForm.locale', 'lt')
        ->set('preferencesForm.timezone', 'Europe/Vilnius')
        ->call('savePreferences')
        ->assertRedirect(route('onboarding.show'))
        ->assertSessionHas('onboarding-focus-step', true);

    $this->get(route('onboarding.show'))
        ->assertOk()
        ->assertSeeHtml('x-init=');
});

test('preference validation is localized in Lithuanian and Russian', function (
    string $locale,
    string $expectedMessageKey,
): void {
    UserOnboarding::factory()->for($this->authenticatedUser)->preferences()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);
    app()->setLocale($locale);

    Livewire::test(Onboarding::class)
        ->set('preferencesForm.locale', 'xx')
        ->set('preferencesForm.timezone', 'Not/A_Timezone')
        ->call('savePreferences')
        ->assertHasErrors(['preferencesForm.locale', 'preferencesForm.timezone'])
        ->assertSee(__($expectedMessageKey));
})->with([
    'Lithuanian' => ['lt', 'auth.settings.validation.locale'],
    'Russian' => ['ru', 'auth.settings.validation.timezone'],
]);

test('a stale wizard redirects to fresh canonical progress without regressing data', function (): void {
    $state = UserOnboarding::factory()->for($this->authenticatedUser)->preferences()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);
    $component = Livewire::test(Onboarding::class);

    $state->forceFill([
        'current_step' => OnboardingStep::PetRelationship,
        'preferences_completed_at' => now(),
        'lock_version' => 3,
    ])->saveOrFail();

    $component
        ->set('preferencesForm.locale', 'ru')
        ->set('preferencesForm.timezone', 'Europe/Riga')
        ->call('savePreferences')
        ->assertRedirect(route('onboarding.show'))
        ->assertSessionHas('feedback', __('onboarding.states.progress_updated'));

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('en')
        ->timezone->toBe('Europe/Vilnius')
        ->and($state->fresh()?->current_step)->toBe(OnboardingStep::PetRelationship)
        ->and($state->fresh()?->lock_version)->toBe(3);
});

test('locked lifecycle snapshots and future methods cannot select or skip a step', function (): void {
    $state = UserOnboarding::factory()->for($this->authenticatedUser)->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);
    $component = Livewire::test(Onboarding::class);

    expect(fn () => $component->set('expectedStep', OnboardingStep::PrivacyDiscovery->value))
        ->toThrow(CannotUpdateLockedPropertyException::class);
    expect(fn () => $component->set('onboardingLockVersion', 999))
        ->toThrow(CannotUpdateLockedPropertyException::class);
    expect(fn () => $component->set('mountedUserId', 999))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    $component
        ->call('savePreferences')
        ->assertHasErrors(['onboarding'])
        ->set('privacyAcknowledged', true)
        ->call('savePrivacy')
        ->assertHasErrors(['onboarding'])
        ->call('deferPetRelationship')
        ->assertHasErrors(['onboarding']);

    expect($state->fresh())
        ->current_step->toBe(OnboardingStep::Introduction)
        ->completed_at->toBeNull()
        ->lock_version->toBe(1);
});

test('invalid pet choice input cannot advance the canonical state', function (): void {
    $state = UserOnboarding::factory()->for($this->authenticatedUser)->petRelationship()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);

    Livewire::test(Onboarding::class)
        ->set('petForm.choice', 'complete')
        ->call('savePetRelationship')
        ->assertHasErrors(['petForm.choice'])
        ->assertDispatched('onboarding-validation-failed');

    expect($state->fresh())
        ->current_step->toBe(OnboardingStep::PetRelationship)
        ->pet_relationship_choice->toBeNull()
        ->lock_version->toBe(3);
});

test('refresh and locale changes render the persisted step without browser-owned navigation', function (
    string $locale,
): void {
    $this->authenticatedUser->forceFill(['locale' => $locale])->saveOrFail();
    UserOnboarding::factory()->for($this->authenticatedUser)->petRelationship()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);
    session()->put('locale', $locale);

    foreach ([1, 2] as $requestNumber) {
        $response = $this->get(route('onboarding.show'))->assertOk();
        $html = (string) $response->getContent();

        expect($html, "request {$requestNumber}")
            ->toContain('lang="'.$locale.'"')
            ->toContain(__('onboarding.steps.pet_relationship.title'))
            ->toContain('data-step="pet-relationship"')
            ->not->toContain('wire:click="back"')
            ->not->toContain('wire:submit="back"')
            ->not->toContain('onboarding.');
    }
})->with(['en', 'lt', 'ru']);

test('the preferences screen query count does not grow with unrelated pet data', function (): void {
    UserOnboarding::factory()->for($this->authenticatedUser)->preferences()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);
    PetProfile::factory()->count(12)->create();
    $queries = [];
    DB::listen(static function ($query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    $this->get(route('onboarding.show'))->assertOk();

    expect($queries)->not->toBeEmpty()
        ->and(count($queries))->toBeLessThanOrEqual(12)
        ->and(collect($queries)->filter(
            static fn (string $query): bool => str_contains($query, 'pet_profiles')
                || str_contains($query, 'pet_profile_access_requests'),
        ))->toBeEmpty();
});

test('the focused onboarding panel follows the flat surface design rule', function (): void {
    $layout = File::get(resource_path('views/components/onboarding-layout.blade.php'));

    expect($layout)
        ->toContain('border border-paw-line')
        ->not->toContain('shadow-panel');
});

test('a signed wizard snapshot cannot mutate a different authenticated account', function (): void {
    UserOnboarding::factory()->for($this->authenticatedUser)->preferences()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);
    $component = Livewire::actingAs($this->authenticatedUser)
        ->test(Onboarding::class)
        ->set('preferencesForm.locale', 'ru')
        ->set('preferencesForm.timezone', 'Europe/Riga');

    $other = User::factory()->onboardingAtPreferences()->create([
        'locale' => 'lt',
        'timezone' => 'Europe/Vilnius',
    ]);
    $this->actingAs($other);
    Livewire::actingAs($other);

    $component->call('savePreferences')->assertForbidden();

    expect($other->fresh())
        ->locale->toBe('lt')
        ->timezone->toBe('Europe/Vilnius')
        ->and($other->onboarding()->firstOrFail()->current_step)->toBe(OnboardingStep::Preferences);
});

test('an onboarding pet snapshot cannot create a profile for a different authenticated account', function (): void {
    $owner = User::factory()->onboardingAtPets()->create();
    $component = Livewire::actingAs($owner)
        ->test(CreatePetProfile::class)
        ->set('form.name', 'Snapshot Pet')
        ->set('form.species', 'dog')
        ->set('form.relationshipRole', 'primary-owner')
        ->set('form.visibility', 'private');

    $other = User::factory()->onboardingAtPets()->create();
    $this->actingAs($other);
    Livewire::actingAs($other);

    $component
        ->call('create')
        ->assertForbidden();

    expect(PetProfile::query()->where('name', 'Snapshot Pet')->exists())->toBeFalse();
});

test('legacy snapshots without an account binding fail closed after deployment', function (): void {
    expect(config('livewire.release_token'))
        ->toBe('2026-08-30-onboarding-account-binding-v1');

    $owner = User::factory()->onboardingAtPreferences()->create();
    $wizard = Livewire::actingAs($owner)
        ->test(Onboarding::class)
        ->set('preferencesForm.locale', 'ru')
        ->set('preferencesForm.timezone', 'Europe/Riga');
    $wizard->instance()->mountedUserId = 0;

    $other = User::factory()->onboardingAtPreferences()->create([
        'locale' => 'lt',
        'timezone' => 'Europe/Vilnius',
    ]);
    $this->actingAs($other);
    Livewire::actingAs($other);

    expect(fn () => $wizard->instance()->hydrate())
        ->toThrow(HttpException::class);

    $petOwner = User::factory()->onboardingAtPets()->create();
    $petWizard = Livewire::actingAs($petOwner)
        ->test(CreatePetProfile::class)
        ->set('form.name', 'Legacy Snapshot Pet')
        ->set('form.species', 'dog')
        ->set('form.relationshipRole', 'primary-owner')
        ->set('form.visibility', 'private');
    $petWizard->instance()->mountedUserId = 0;

    $petTarget = User::factory()->onboardingAtPets()->create();
    $this->actingAs($petTarget);
    Livewire::actingAs($petTarget);

    expect(fn () => $petWizard->instance()->hydrate())
        ->toThrow(HttpException::class);

    expect(PetProfile::query()->where('name', 'Legacy Snapshot Pet')->exists())->toBeFalse();
});

test('direct deferral cannot replace a still-valid managed pet decision', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->privacyDiscovery()
        ->state(['pet_relationship_choice' => OnboardingPetChoice::ManagedPet])
        ->create();
    $pet = PetProfile::factory()->for($this->authenticatedUser)->privateProfile()->create();
    PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($this->authenticatedUser)
        ->create();

    expect(fn () => app(DeferOnboardingPetRelationship::class)->handle(
        $this->authenticatedUser,
        $state->lock_version,
    ))->toThrow(ValidationException::class);

    expect($state->fresh())
        ->pet_relationship_choice->toBe(OnboardingPetChoice::ManagedPet)
        ->lock_version->toBe(4);
});

test('direct deferral cannot replace a still-valid access request decision', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->privacyDiscovery()
        ->state(['pet_relationship_choice' => OnboardingPetChoice::AccessRequested])
        ->create();
    PetProfileAccessRequest::factory()
        ->for($this->authenticatedUser, 'requester')
        ->create();

    expect(fn () => app(DeferOnboardingPetRelationship::class)->handle(
        $this->authenticatedUser,
        $state->lock_version,
    ))->toThrow(ValidationException::class);

    expect($state->fresh())
        ->pet_relationship_choice->toBe(OnboardingPetChoice::AccessRequested)
        ->lock_version->toBe(4);
});

test('revoked pet evidence is reported on the accessible pet choice field', function (): void {
    UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->petRelationship()
        ->create();
    $request = PetProfileAccessRequest::factory()
        ->for($this->authenticatedUser, 'requester')
        ->create();

    $component = Livewire::test(Onboarding::class)
        ->set('petForm.choice', OnboardingPetChoice::AccessRequested->value);

    $request->deleteOrFail();

    $component
        ->call('savePetRelationship')
        ->assertHasErrors(['petForm.choice'])
        ->assertSeeHtml('id="onboarding-pet-choice-error"')
        ->assertSeeHtml('aria-invalid="true"');
});

test('the canonical browser wrapper exposes an isolated onboarding mode', function (): void {
    $source = File::get(base_path('scripts/run-browser-check.php'));
    $browserSource = File::get(base_path('scripts/accessibility-browser-check.mjs'));
    $package = File::get(base_path('package.json'));

    expect($source)
        ->toContain("'onboarding' => ['scripts/accessibility-browser-check.mjs', '--onboarding-only']")
        ->toContain("'EMAIL_VERIFICATION_ENABLED' => 'true'")
        ->toContain("'-S',")
        ->toContain("'127.0.0.1:'.\$portMatch[1]")
        ->toContain("/resources/server.php'")
        ->not->toContain("[PHP_BINARY, 'artisan', 'serve'")
        ->and($package)->toContain('"test:browser:onboarding": "php scripts/run-browser-check.php onboarding"')
        ->and($browserSource)
        ->toContain('await setViewport(320, 800, true)')
        ->toContain('await setViewport(360, 800, true)')
        ->toContain('await setViewport(375, 812, true)')
        ->toContain('await setViewport(390, 844, true)')
        ->toContain('await setViewport(768, 1024, false)')
        ->toContain('await setViewport(1440, 900, false)')
        ->toContain("location.pathname === '/'")
        ->toContain('privacyAcknowledged')
        ->toContain('savePrivacy');

    $result = Process::path(base_path())
        ->timeout(30)
        ->run([PHP_BINARY, 'scripts/run-browser-check.php', 'onboarding', '--assert-isolation']);

    expect($result->successful(), $result->errorOutput())->toBeTrue();
});

test('the isolated onboarding browser fixtures are repeatable without duplicates', function (): void {
    $seeder = app(OnboardingBrowserSeeder::class);

    $seeder->run();
    $seeder->run();

    $users = User::query()
        ->whereIn('email', [
            'onboarding-browser@example.test',
            'onboarding-browser-ru@example.test',
        ])
        ->with(['onboarding', 'socialActor.settings'])
        ->get();

    expect($users)->toHaveCount(2);

    foreach ($users as $user) {
        expect($user->onboarding)->not->toBeNull()
            ->and($user->socialActor)->not->toBeNull()
            ->and($user->socialActor?->settings)->not->toBeNull();
    }
});
