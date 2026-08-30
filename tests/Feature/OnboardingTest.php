<?php

declare(strict_types=1);

use App\Actions\AdvanceUserOnboarding;
use App\Actions\CompleteOnboardingPreferences;
use App\Actions\CompleteOnboardingPrivacy;
use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Http\Middleware\EnsureOnboardingIsComplete;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Onboarding;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\SocialActor;
use App\Models\SocialActorSetting;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\SocialActorResolver;
use Illuminate\Cache\RateLimiter;
use Illuminate\Database\QueryException;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

test('registration atomically provisions private onboarding identity', function (bool $verificationEnabled, string $destination): void {
    Notification::fake();
    config()->set('platform.email_verification_enabled', $verificationEnabled);
    auth()->logout();

    Livewire::test(Register::class)
        ->set('form.name', 'Onboarding Member')
        ->set('form.email', 'onboarding@example.test')
        ->set('form.password', 'Secure-Paw-2026')
        ->set('form.password_confirmation', 'Secure-Paw-2026')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route($destination));

    $user = User::query()->where('email', 'onboarding@example.test')->firstOrFail();
    $onboarding = UserOnboarding::query()->whereBelongsTo($user)->firstOrFail();
    $actor = SocialActor::query()->whereBelongsTo($user)->firstOrFail();
    $settings = SocialActorSetting::query()->whereBelongsTo($actor, 'actor')->firstOrFail();

    expect($onboarding->current_step)->toBe(OnboardingStep::Introduction)
        ->and($onboarding->completed_at)->toBeNull()
        ->and($onboarding->lock_version)->toBe(1)
        ->and($actor->is_discoverable)->toBeFalse()
        ->and($settings->is_recommendable)->toBeFalse()
        ->and($settings->allow_message_requests)->toBeFalse()
        ->and($user->onboarding()->count())->toBe(1)
        ->and($user->socialActor()->count())->toBe(1)
        ->and($actor->settings()->count())->toBe(1);
})->with([
    'verification enabled' => [true, 'verification.notice'],
    'verification disabled' => [false, 'onboarding.show'],
]);

test('onboarding state is unique per user and cascades with the account', function (): void {
    $user = User::factory()->create();
    UserOnboarding::factory()->for($user)->create();

    expect(fn () => UserOnboarding::factory()->for($user)->create())
        ->toThrow(QueryException::class);

    $user->delete();

    expect(UserOnboarding::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('introduction transition is forward only replay safe and versioned', function (): void {
    $state = UserOnboarding::factory()->for($this->authenticatedUser)->create();
    $this->freezeTime();

    $advanced = app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::Introduction,
        1,
    );
    $firstTimestamp = $advanced->introduction_completed_at;

    expect($advanced->current_step)->toBe(OnboardingStep::Preferences)
        ->and($firstTimestamp)->not->toBeNull()
        ->and($advanced->lock_version)->toBe(2);

    $replayed = app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::Introduction,
        1,
    );

    expect($replayed->current_step)->toBe(OnboardingStep::Preferences)
        ->and($replayed->introduction_completed_at?->equalTo($firstTimestamp))->toBeTrue()
        ->and($replayed->lock_version)->toBe(2)
        ->and($state->fresh()?->lock_version)->toBe(2);
});

test('pet relationship transition requires canonical evidence or an explicit deferral', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->petRelationship()
        ->create();

    app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::NotNow,
    );

    expect($state->fresh())
        ->current_step->toBe(OnboardingStep::PrivacyDiscovery)
        ->pet_relationship_choice->toBe(OnboardingPetChoice::NotNow)
        ->pet_relationship_completed_at->not->toBeNull();
});

test('legacy users without onboarding state retain portal access', function (): void {
    expect($this->authenticatedUser->onboarding()->exists())->toBeFalse();

    $this->get(route('content.index'))->assertSuccessful();
});

test('incomplete users are redirected to onboarding before product binding', function (): void {
    UserOnboarding::factory()->for($this->authenticatedUser)->create();
    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->get(route('content.show', ['contentPublication' => 'missing-onboarding-publication']))
        ->assertRedirect(route('onboarding.show'));

    expect(collect(DB::getQueryLog())->contains(
        fn (array $query): bool => str_contains($query['query'], 'content_publications'),
    ))->toBeFalse();
});

test('incomplete product mutations and json requests fail closed', function (): void {
    UserOnboarding::factory()->for($this->authenticatedUser)->create();

    $this->post(route('forum.actions'), [])
        ->assertStatus(409);

    $this->getJson(route('content.index'))
        ->assertStatus(409)
        ->assertJsonPath('code', 'onboarding_required')
        ->assertJsonPath('onboarding_url', route('onboarding.show'))
        ->assertJsonMissingPath('data');
});

test('onboarding middleware runs after portal access before bindings and persists for livewire', function (): void {
    $route = Route::getRoutes()->getByName('content.show');
    $middleware = app('router')->gatherRouteMiddleware($route);
    $onboardingPosition = array_search(EnsureOnboardingIsComplete::class, $middleware, true);
    $bindingPosition = array_search(SubstituteBindings::class, $middleware, true);

    expect($onboardingPosition)->toBeInt()
        ->and($bindingPosition)->toBeInt()
        ->and($onboardingPosition)->toBeLessThan($bindingPosition)
        ->and(Livewire::getPersistentMiddleware())
        ->toContain(EnsureOnboardingIsComplete::class);
});

test('preferences and privacy complete through named livewire mutations', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->preferences()
        ->create();
    $actor = app(SocialActorResolver::class)
        ->provisionPrivateForUser($this->authenticatedUser);

    Livewire::test(Onboarding::class)
        ->assertSet('expectedStep', OnboardingStep::Preferences->value)
        ->set('preferencesForm.locale', 'ru')
        ->set('preferencesForm.timezone', 'Europe/Riga')
        ->call('savePreferences')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.show'));

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('ru')
        ->timezone->toBe('Europe/Riga')
        ->and(session('locale'))->toBe('ru')
        ->and($state->fresh()?->current_step)->toBe(OnboardingStep::PetRelationship);

    $state->refresh()->forceFill([
        'current_step' => OnboardingStep::PrivacyDiscovery,
        'pet_relationship_choice' => OnboardingPetChoice::NotNow,
        'pet_relationship_completed_at' => now(),
        'lock_version' => 4,
    ])->saveOrFail();
    app()->setLocale('ru');

    Livewire::test(Onboarding::class)
        ->set('privacyForm.isDiscoverable', true)
        ->set('privacyForm.isRecommendable', true)
        ->set('privacyForm.allowMessageRequests', false)
        ->call('savePrivacy')
        ->assertHasNoErrors()
        ->assertRedirect(route('home'));

    expect($state->fresh()?->isComplete())->toBeTrue()
        ->and($actor->fresh()?->is_discoverable)->toBeTrue()
        ->and($actor->settings()->firstOrFail()->is_recommendable)->toBeTrue()
        ->and($actor->settings()->firstOrFail()->allow_message_requests)->toBeFalse();
});

test('onboarding validates acknowledgement preferences and stale snapshots', function (): void {
    $state = UserOnboarding::factory()->for($this->authenticatedUser)->create();
    app(SocialActorResolver::class)
        ->provisionPrivateForUser($this->authenticatedUser);

    Livewire::test(Onboarding::class)
        ->call('acknowledgeIntroduction')
        ->assertHasErrors(['introductionAcknowledged'])
        ->set('introductionAcknowledged', true)
        ->call('acknowledgeIntroduction')
        ->assertHasNoErrors();

    $stale = Livewire::test(Onboarding::class);
    $state->refresh()->forceFill([
        'current_step' => OnboardingStep::PetRelationship,
        'preferences_completed_at' => now(),
        'lock_version' => 3,
    ])->saveOrFail();

    $stale
        ->set('preferencesForm.locale', 'unsupported')
        ->set('preferencesForm.timezone', 'not-a-timezone')
        ->call('savePreferences')
        ->assertHasErrors([
            'preferencesForm.locale',
            'preferencesForm.timezone',
        ]);
});

test('onboarding page has a dedicated accessible account flow shell', function (): void {
    UserOnboarding::factory()->for($this->authenticatedUser)->create();
    app(SocialActorResolver::class)
        ->provisionPrivateForUser($this->authenticatedUser);

    $response = $this->get(route('onboarding.show'))
        ->assertOk()
        ->assertSee('data-section="onboarding"', false)
        ->assertSee('aria-current="step"', false)
        ->assertSee('wire:offline', false)
        ->assertSee('wire:loading', false);

    $html = (string) $response->getContent();

    expect(substr_count($html, '<main'))->toBe(1)
        ->and(substr_count($html, '<h1'))->toBe(1)
        ->and($html)->not->toContain('data-app-shell');
});

test('login preserves an intended route until onboarding completion', function (): void {
    $user = User::factory()->create(['password' => 'Secure-Paw-2026']);
    UserOnboarding::factory()->for($user)->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($user);
    auth()->logout();
    session()->put('url.intended', route('devices.index'));

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'Secure-Paw-2026')
        ->call('authenticate')
        ->assertRedirect(route('onboarding.show'));

    expect(session('url.intended'))->toBe(route('devices.index'));
});

test('email verification preserves a protected destination and hands off to onboarding', function (): void {
    $user = User::factory()->unverified()->create();
    UserOnboarding::factory()->for($user)->create();
    app(\App\Services\SocialActorResolver::class)->provisionPrivateForUser($user);
    $this->actingAs($user);

    $this->get(route('devices.index'))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas('url.intended', route('devices.index'));

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $this->get($verificationUrl)
        ->assertRedirect(route('onboarding.show'));

    expect(session('url.intended'))->toBe(route('devices.index'))
        ->and($user->fresh()?->hasVerifiedEmail())->toBeTrue();
});

test('safe completion rejects an external intended destination', function (): void {
    UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->privacyDiscovery()
        ->create();
    app(SocialActorResolver::class)
        ->provisionPrivateForUser($this->authenticatedUser);
    session()->put('url.intended', 'https://attacker.example/steal');

    Livewire::test(Onboarding::class)
        ->call('savePrivacy')
        ->assertRedirect(route('home'));

    expect(session()->has('url.intended'))->toBeFalse();
});

test('stale authenticated guest components cannot switch or create accounts', function (): void {
    $userCount = User::query()->count();

    Livewire::test(Register::class)
        ->set('form.name', 'Unexpected Account')
        ->set('form.email', 'unexpected@example.test')
        ->set('form.password', 'Secure-Paw-2026')
        ->set('form.password_confirmation', 'Secure-Paw-2026')
        ->call('register')
        ->assertForbidden();

    Livewire::test(Login::class)
        ->set('form.email', $this->authenticatedUser->email)
        ->set('form.password', 'password')
        ->call('authenticate')
        ->assertForbidden();

    expect(User::query()->count())->toBe($userCount);
    $this->assertAuthenticatedAs($this->authenticatedUser);
});

test('registration and verification resend mutations are rate limited', function (): void {
    Notification::fake();
    $limiter = app(RateLimiter::class);
    $limiter->clear('registration|127.0.0.1');
    auth()->logout();

    foreach (range(1, 5) as $attempt) {
        Livewire::test(Register::class)
            ->set('form.name', 'Member '.$attempt)
            ->set('form.email', 'member-'.$attempt.'@example.test')
            ->set('form.password', 'Secure-Paw-2026')
            ->set('form.password_confirmation', 'Secure-Paw-2026')
            ->call('register')
            ->assertHasNoErrors();
        auth()->logout();
    }

    Livewire::test(Register::class)
        ->set('form.name', 'Blocked Member')
        ->set('form.email', 'blocked-registration@example.test')
        ->set('form.password', 'Secure-Paw-2026')
        ->set('form.password_confirmation', 'Secure-Paw-2026')
        ->call('register')
        ->assertHasErrors(['form.email']);

    expect(User::query()->where('email', 'blocked-registration@example.test')->exists())
        ->toBeFalse();

    $unverified = User::factory()->unverified()->create();
    $this->actingAs($unverified);
    $resendKey = 'verification-resend|'.$unverified->id.'|127.0.0.1';
    $limiter->clear($resendKey);

    foreach (range(1, 3) as $_attempt) {
        Livewire::test(VerifyEmail::class)
            ->call('resend')
            ->assertHasNoErrors();
    }

    Livewire::test(VerifyEmail::class)
        ->call('resend')
        ->assertHasErrors(['resend']);
});

test('managed pets and pending access requests are verified from canonical relationships', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->petRelationship()
        ->create();

    expect(fn () => app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::ManagedPet,
    ))->toThrow(ValidationException::class);

    PetProfile::factory()->for($this->authenticatedUser)->privateProfile()->create();
    app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::ManagedPet,
    );

    expect($state->fresh()?->pet_relationship_choice)
        ->toBe(OnboardingPetChoice::ManagedPet);

    $requester = User::factory()->create();
    $requesterState = UserOnboarding::factory()
        ->for($requester)
        ->petRelationship()
        ->create();
    PetProfileAccessRequest::factory()->for($requester, 'requester')->create();
    $this->actingAs($requester);

    app(AdvanceUserOnboarding::class)->handle(
        $requester,
        OnboardingStep::PetRelationship,
        $requesterState->lock_version,
        OnboardingPetChoice::AccessRequested,
    );

    expect($requesterState->fresh()?->pet_relationship_choice)
        ->toBe(OnboardingPetChoice::AccessRequested);
});

test('stale account and social versions roll back onboarding side effects', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->preferences()
        ->create();
    $actor = app(SocialActorResolver::class)
        ->provisionPrivateForUser($this->authenticatedUser);

    expect(fn () => app(CompleteOnboardingPreferences::class)->handle(
        $this->authenticatedUser,
        ['locale' => 'ru', 'timezone' => 'Europe/Riga'],
        OnboardingStep::Preferences,
        $state->lock_version + 1,
    ))->toThrow(ValidationException::class);

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('en')
        ->timezone->toBe('Europe/Vilnius');

    $state->forceFill([
        'current_step' => OnboardingStep::PrivacyDiscovery,
        'pet_relationship_choice' => OnboardingPetChoice::NotNow,
        'pet_relationship_completed_at' => now(),
        'lock_version' => 4,
    ])->saveOrFail();
    $settings = $actor->settings()->firstOrFail();

    expect(fn () => app(CompleteOnboardingPrivacy::class)->handle(
        $this->authenticatedUser,
        true,
        true,
        true,
        OnboardingStep::PrivacyDiscovery,
        $state->lock_version,
        $settings->lock_version + 1,
    ))->toThrow(ValidationException::class);

    expect($state->fresh()?->isComplete())->toBeFalse()
        ->and($actor->fresh()?->is_discoverable)->toBeFalse()
        ->and($settings->fresh()?->is_recommendable)->toBeFalse()
        ->and($settings->fresh()?->allow_message_requests)->toBeFalse();
});
