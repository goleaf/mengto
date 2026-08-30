<?php

declare(strict_types=1);

use App\Actions\CreatePetProfile as CreatePetProfileAction;
use App\Actions\SubmitPetProfileAccessRequest;
use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Enums\PetProfileAccessRequestType;
use App\Enums\UserStatus;
use App\Http\Middleware\EnsureOnboardingIsComplete;
use App\Http\Middleware\RequirePortalAccess;
use App\Http\Middleware\SetLocale;
use App\Livewire\Pets\CreatePetProfile;
use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

test('verified incomplete accounts are redirected from representative portal routes', function (string $routeName): void {
    $user = User::factory()->onboardingIncomplete()->create();
    $this->actingAs($user);

    $this->get(route($routeName))
        ->assertRedirect(route('onboarding.show'));
})->with([
    'home' => ['home'],
    'content' => ['content.index'],
    'messages' => ['messages.index'],
    'pet workspace' => ['pets.index'],
    'places' => ['places.index'],
    'events' => ['meetups.index'],
    'medical' => ['medical-records.index'],
    'devices' => ['devices.index'],
    'marketplace' => ['marketplace.index'],
    'profile settings' => ['profile.settings'],
]);

test('pet creation bridge rejects transport outside the pet relationship step', function (string $step): void {
    $user = match ($step) {
        'preferences' => User::factory()->onboardingAtPreferences()->create(),
        'privacy' => User::factory()->onboardingAtPrivacy()->create(),
        default => User::factory()->onboardingIncomplete()->create(),
    };
    $this->actingAs($user);

    $this->get(route('pets.manage.create'))
        ->assertRedirect(route('onboarding.show'));

    $this->post(route('livewire.upload-file'))
        ->assertStatus(409)
        ->assertJsonPath('code', 'onboarding_required');

    $this->get(route('livewire.preview-file', ['filename' => 'missing-preview']))
        ->assertRedirect(route('onboarding.show'));
})->with(['introduction', 'preferences', 'privacy']);

test('pet relationship step can render the canonical pet creation bridge', function (): void {
    $petRelationship = User::factory()->onboardingAtPets()->create();
    $this->actingAs($petRelationship);

    $this->get(route('pets.manage.create'))
        ->assertSuccessful();
});

test('pet setup policy preserves every supported account compatibility mode', function (string $mode): void {
    if ($mode === 'verification-disabled') {
        config()->set('platform.email_verification_enabled', false);
    }

    $user = match ($mode) {
        'completed' => User::factory()->onboarded()->create(),
        'verification-disabled' => User::factory()->unverified()->onboardingAtPets()->create(),
        default => User::factory()->create(),
    };
    $profile = PetProfile::factory()->discoverable()->create();

    expect(Gate::forUser($user)->allows('create', PetProfile::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('requestAccess', $profile))->toBeTrue();
})->with(['legacy', 'completed', 'verification-disabled']);

test('pet creation component independently enforces the current lifecycle state', function (): void {
    $introduction = User::factory()->onboardingIncomplete()->create();

    Livewire::actingAs($introduction)
        ->test(CreatePetProfile::class)
        ->assertForbidden();

    $unverified = User::factory()->unverified()->onboardingAtPets()->create();

    Livewire::actingAs($unverified)
        ->test(CreatePetProfile::class)
        ->assertForbidden();

    $petRelationship = User::factory()->onboardingAtPets()->create();
    $component = Livewire::actingAs($petRelationship)
        ->test(CreatePetProfile::class)
        ->assertOk();
    $state = $petRelationship->onboarding()->firstOrFail();
    $state->forceFill([
        'current_step' => OnboardingStep::PrivacyDiscovery,
        'pet_relationship_choice' => OnboardingPetChoice::NotNow,
        'pet_relationship_completed_at' => now(),
        'lock_version' => 4,
    ])->saveOrFail();

    $component
        ->call('create')
        ->assertForbidden();

    expect(PetProfile::query()->where('user_id', $petRelationship->id)->exists())
        ->toBeFalse();
});

test('a stale pet creation livewire snapshot is denied after onboarding advances', function (): void {
    $user = User::factory()->onboardingAtPets()->create();
    $this->actingAs($user);
    $page = $this->get(route('pets.manage.create'))->assertSuccessful();
    $snapshot = htmlspecialchars_decode(
        (string) str((string) $page->getContent())->betweenFirst('wire:snapshot="', '"'),
        ENT_QUOTES | ENT_SUBSTITUTE,
    );
    $state = $user->onboarding()->firstOrFail();
    $state->forceFill([
        'current_step' => OnboardingStep::PrivacyDiscovery,
        'pet_relationship_choice' => OnboardingPetChoice::NotNow,
        'pet_relationship_completed_at' => now(),
        'lock_version' => 4,
    ])->saveOrFail();

    $this->withHeader('X-Livewire', 'true')
        ->postJson(route('default-livewire.update'), [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => [],
                'calls' => [[
                    'method' => 'create',
                    'params' => [],
                    'path' => '',
                ]],
            ]],
        ])
        ->assertStatus(409);

    expect(PetProfile::query()->where('user_id', $user->id)->exists())
        ->toBeFalse();
});

test('onboarding pet creation returns to onboarding without consuming the product destination', function (): void {
    $user = User::factory()->onboardingAtPets()->create();
    $this->actingAs($user);
    session()->put('url.intended', route('devices.index'));

    Livewire::test(CreatePetProfile::class)
        ->set('form.name', 'Onboarding Baks')
        ->set('form.species', 'dog')
        ->set('form.relationshipRole', 'primary-owner')
        ->set('form.visibility', 'private')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.show'));

    expect(PetProfile::query()->where('user_id', $user->id)->exists())->toBeTrue()
        ->and(session('url.intended'))->toBe(route('devices.index'));

    $this->get(route('onboarding.show'))
        ->assertSuccessful()
        ->assertSee(__('pet_profiles.feedback.created'));
});

test('onboarding duplicate access requests return with visible feedback', function (): void {
    $user = User::factory()->onboardingAtPets()->create();
    $profile = PetProfile::factory()->discoverable()->create([
        'name' => 'Baks',
        'species' => 'dog',
    ]);

    Livewire::actingAs($user)
        ->test(CreatePetProfile::class)
        ->set('form.name', 'Baks')
        ->set('form.species', 'dog')
        ->set('form.relationshipRole', 'co-owner')
        ->set('form.visibility', 'private')
        ->call('create')
        ->call('startAccessRequest', $profile->profile_key)
        ->set('accessRequestForm.requestType', 'co-ownership')
        ->set(
            'accessRequestForm.evidenceSummary',
            'I share daily care and can provide relationship evidence privately.',
        )
        ->call('submitSelectedAccessRequest')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.show'));

    $this->get(route('onboarding.show'))
        ->assertSuccessful()
        ->assertSee(__('pet_profiles.feedback.access_request_submitted'));
});

test('pet setup actions enforce the lifecycle boundary without a livewire caller', function (string $variant): void {
    $user = match ($variant) {
        'unverified' => User::factory()->unverified()->onboardingAtPets()->create(),
        'stale-blocked' => User::factory()->create(),
        default => User::factory()->onboardingIncomplete()->create(),
    };
    $profile = PetProfile::factory()->discoverable()->create();
    $this->actingAs($user);

    if ($variant === 'stale-blocked') {
        User::query()->whereKey($user->id)->update(['status' => UserStatus::Blocked]);
    }

    expect(fn () => app(CreatePetProfileAction::class)->handle([
        'title' => 'Forbidden Pet',
        'species' => 'dog',
        'idempotency_key' => 'forbidden-direct-pet-create',
    ]))->toThrow(AuthorizationException::class)
        ->and(fn () => app(SubmitPetProfileAccessRequest::class)->handle(
            $profile,
            PetProfileAccessRequestType::CoOwnership,
            null,
            'I can provide private supporting evidence for the relationship review.',
            null,
            'forbidden-direct-access-request',
        ))->toThrow(AuthorizationException::class)
        ->and(PetProfile::query()->where('name', 'Forbidden Pet')->exists())->toBeFalse();
})->with(['wrong onboarding step', 'unverified', 'stale-blocked']);

test('email verification precedes onboarding for every protected route', function (string $routeName): void {
    $user = User::factory()->unverified()->onboardingIncomplete()->create();
    $this->actingAs($user);

    $this->get(route($routeName))
        ->assertRedirect(route('verification.notice'));
})->with([
    'onboarding' => ['onboarding.show'],
    'portal' => ['content.index'],
    'pet bridge' => ['pets.manage.create'],
]);

test('inactive status precedes onboarding', function (UserStatus $status): void {
    $user = User::factory()->onboardingIncomplete()->create(['status' => $status]);
    $this->actingAs($user);

    $this->get(route('onboarding.show'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
})->with([
    'blocked' => [UserStatus::Blocked],
    'suspended' => [UserStatus::Suspended],
]);

test('json portal requests receive a structured onboarding denial', function (): void {
    $user = User::factory()->onboardingIncomplete()->create();
    $this->actingAs($user);

    $this->getJson(route('content.index'))
        ->assertStatus(409)
        ->assertExactJson([
            'code' => 'onboarding_required',
            'message' => __('onboarding.middleware.incomplete_detail'),
            'onboarding_url' => route('onboarding.show'),
        ]);
});

test('json verification denial precedes onboarding without returning html', function (): void {
    $user = User::factory()->unverified()->onboardingIncomplete()->create();
    $this->actingAs($user);

    $this->getJson(route('content.index'))
        ->assertStatus(409)
        ->assertExactJson([
            'code' => 'email_verification_required',
            'message' => __('auth.verification.required'),
            'verification_url' => route('verification.notice'),
        ]);
});

test('lifecycle json denials use the authenticated account locale', function (string $locale, string $factoryState): void {
    $factory = User::factory()->state(['locale' => $locale]);
    $user = $factoryState === 'unverified'
        ? $factory->unverified()->onboardingIncomplete()->create()
        : $factory->onboardingIncomplete()->create();
    $this->actingAs($user);

    $response = $this->getJson(route('content.index'))->assertStatus(409);
    $translationKey = $factoryState === 'unverified'
        ? 'auth.verification.required'
        : 'onboarding.middleware.incomplete_detail';

    $response->assertJsonPath('message', Lang::get($translationKey, locale: $locale));
})->with([
    'lt onboarding' => ['lt', 'onboarding'],
    'ru onboarding' => ['ru', 'onboarding'],
    'lt verification' => ['lt', 'unverified'],
    'ru verification' => ['ru', 'unverified'],
]);

test('locale and lifecycle middleware run before bindings in canonical order', function (): void {
    $route = Route::getRoutes()->getByName('content.show');
    $middleware = app('router')->gatherRouteMiddleware($route);
    $localePosition = array_search(SetLocale::class, $middleware, true);
    $portalPosition = array_search(RequirePortalAccess::class, $middleware, true);
    $onboardingPosition = array_search(EnsureOnboardingIsComplete::class, $middleware, true);
    $bindingPosition = array_search(SubstituteBindings::class, $middleware, true);

    expect($localePosition)->toBeInt()
        ->and($portalPosition)->toBeInt()
        ->and($onboardingPosition)->toBeInt()
        ->and($bindingPosition)->toBeInt()
        ->and($localePosition)->toBeLessThan($portalPosition)
        ->and($portalPosition)->toBeLessThan($onboardingPosition)
        ->and($onboardingPosition)->toBeLessThan($bindingPosition);
});

test('completed and legacy accounts remain outside the onboarding boundary', function (string $factoryState): void {
    $factory = User::factory();
    $user = $factoryState === 'onboarded'
        ? $factory->onboarded()->create()
        : $factory->create();
    $this->actingAs($user);

    $this->get(route('content.index'))->assertSuccessful();
    $this->get(route('onboarding.show'))->assertRedirect(route('home'));
})->with([
    'explicitly complete' => ['onboarded'],
    'legacy complete' => ['legacy'],
]);
