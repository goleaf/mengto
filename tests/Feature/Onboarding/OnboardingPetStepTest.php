<?php

declare(strict_types=1);

use App\Actions\AdvanceUserOnboarding;
use App\Actions\CompleteOnboardingPrivacy;
use App\Actions\RevisitOnboardingPetRelationship;
use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\PetProfileAccessRequestStatus;
use App\Enums\PetProfilePermission;
use App\Enums\PetProfileStatus;
use App\Enums\PetProfileVisibility;
use App\Livewire\Onboarding;
use App\Livewire\Pets\CreatePetProfile;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\PetProfileSlugAlias;
use App\Models\User;
use App\Services\OnboardingPetEvidence;
use App\Services\SocialActorResolver;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('pet choices preserve legacy rows while distinguishing no pet from add later', function (): void {
    expect(OnboardingPetChoice::cases())
        ->toContain(OnboardingPetChoice::ManagedPet)
        ->toContain(OnboardingPetChoice::AccessRequested)
        ->toContain(OnboardingPetChoice::NoPet)
        ->toContain(OnboardingPetChoice::AddLater)
        ->toContain(OnboardingPetChoice::NotNow)
        ->and(OnboardingPetChoice::tryFrom('not-now'))->toBe(OnboardingPetChoice::NotNow)
        ->and(OnboardingPetChoice::NoPet->value)->toBe('no-pet')
        ->and(OnboardingPetChoice::AddLater->value)->toBe('add-later');
});

test('the legacy not now value remains readable but cannot be written by new transitions', function (): void {
    $user = User::factory()->onboardingAtPets()->create();
    $state = $user->onboarding()->firstOrFail();
    $this->actingAs($user);

    expect(fn () => app(AdvanceUserOnboarding::class)->handle(
        $user,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::NotNow,
    ))->toThrow(ValidationException::class);

    Livewire::actingAs($user)
        ->test(Onboarding::class)
        ->set('petForm.choice', OnboardingPetChoice::NotNow->value)
        ->call('savePetRelationship')
        ->assertHasErrors(['petForm.choice']);

    expect($state->fresh()?->current_step)->toBe(OnboardingStep::PetRelationship)
        ->and($state->fresh()?->pet_relationship_choice)->toBeNull();
});

test('a migrated legacy not now row can still complete onboarding', function (): void {
    $user = User::factory()->onboardingAtPrivacy()->create();
    $state = $user->onboarding()->firstOrFail();
    $state->forceFill(['pet_relationship_choice' => OnboardingPetChoice::NotNow])->saveOrFail();
    $this->actingAs($user);
    $actor = app(SocialActorResolver::class)->provisionPrivateForUser($user);
    $settings = $actor->settings()->firstOrFail();

    $completed = app(CompleteOnboardingPrivacy::class)->handle(
        user: $user,
        privacyAcknowledged: true,
        isDiscoverable: false,
        isRecommendable: false,
        allowMessageRequests: false,
        expectedStep: OnboardingStep::PrivacyDiscovery,
        expectedOnboardingLockVersion: $state->lock_version,
        expectedSocialSettingsLockVersion: $settings->lock_version,
    );

    expect($completed->isComplete())->toBeTrue()
        ->and($completed->pet_relationship_choice)->toBe(OnboardingPetChoice::NotNow);
});

test('no pet and add later advance without creating pet domain records', function (string $choiceValue): void {
    $user = User::factory()->onboardingAtPets()->create();
    $state = $user->onboarding()->firstOrFail();
    $this->actingAs($user);
    $choice = OnboardingPetChoice::from($choiceValue);

    $advanced = app(AdvanceUserOnboarding::class)->handle(
        $user,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        $choice,
    );

    expect($advanced->current_step)->toBe(OnboardingStep::PrivacyDiscovery)
        ->and($advanced->pet_relationship_choice)->toBe($choice)
        ->and(PetProfile::query()->count())->toBe(0)
        ->and(PetProfileManager::query()->count())->toBe(0)
        ->and(PetProfileAccessRequest::query()->count())->toBe(0);
})->with([
    'no pet' => ['no-pet'],
    'add later' => ['add-later'],
]);

test('manage pet remains unresolved without canonical relationship evidence', function (): void {
    $user = User::factory()->onboardingAtPets()->create();
    $state = $user->onboarding()->firstOrFail();
    $this->actingAs($user);

    expect(fn () => app(AdvanceUserOnboarding::class)->handle(
        $user,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::ManagedPet,
    ))->toThrow(ValidationException::class);

    expect($state->fresh()?->current_step)->toBe(OnboardingStep::PetRelationship)
        ->and($state->fresh()?->pet_relationship_choice)->toBeNull();
});

test('only active shared management satisfies the manage pet decision', function (
    PetManagerStatus $status,
    ?string $timeBoundary,
): void {
    $user = User::factory()->onboardingAtPets()->create();
    $owner = User::factory()->create();
    $profile = PetProfile::factory()->for($owner)->privateProfile()->create();
    $manager = PetProfileManager::factory()
        ->for($profile, 'profile')
        ->for($user)
        ->create(['status' => $status]);

    if ($timeBoundary === 'future') {
        $manager->forceFill(['starts_at' => now()->addMinute()])->saveOrFail();
    }

    if ($timeBoundary === 'expired') {
        $manager->forceFill(['ends_at' => now()])->saveOrFail();
    }

    if ($timeBoundary === 'revoked') {
        $manager->forceFill(['revoked_at' => now()])->saveOrFail();
    }

    $state = $user->onboarding()->firstOrFail();
    $this->actingAs($user);

    expect(fn () => app(AdvanceUserOnboarding::class)->handle(
        $user,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::ManagedPet,
    ))->toThrow(ValidationException::class);

    expect($state->fresh()?->current_step)->toBe(OnboardingStep::PetRelationship);
})->with([
    'invited' => [PetManagerStatus::Invited, null],
    'suspended' => [PetManagerStatus::Suspended, null],
    'revoked status' => [PetManagerStatus::Revoked, null],
    'expired status' => [PetManagerStatus::Expired, null],
    'declined' => [PetManagerStatus::Declined, null],
    'future start' => [PetManagerStatus::Active, 'future'],
    'end boundary' => [PetManagerStatus::Active, 'expired'],
    'revocation timestamp' => [PetManagerStatus::Active, 'revoked'],
]);

test('an active shared manager and a legacy owner can each resolve manage pet', function (string $relationship): void {
    $user = User::factory()->onboardingAtPets()->create();
    $state = $user->onboarding()->firstOrFail();

    if ($relationship === 'active manager') {
        $profile = PetProfile::factory()->privateProfile()->create();
        PetProfileManager::factory()->for($profile, 'profile')->for($user)->create();
    } else {
        PetProfile::factory()->for($user)->privateProfile()->create();
    }

    $this->actingAs($user);
    $advanced = app(AdvanceUserOnboarding::class)->handle(
        $user,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::ManagedPet,
    );

    expect($advanced->current_step)->toBe(OnboardingStep::PrivacyDiscovery)
        ->and($advanced->pet_relationship_choice)->toBe(OnboardingPetChoice::ManagedPet);
})->with(['active manager', 'legacy owner']);

test('a current pending access request resolves care intent without granting management', function (): void {
    $user = User::factory()->onboardingAtPets()->create();
    $profile = PetProfile::factory()->discoverable()->create();
    PetProfileAccessRequest::factory()
        ->for($profile, 'profile')
        ->for($user, 'requester')
        ->create(['status' => PetProfileAccessRequestStatus::Pending]);
    $state = $user->onboarding()->firstOrFail();
    $this->actingAs($user);

    $advanced = app(AdvanceUserOnboarding::class)->handle(
        $user,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::AccessRequested,
    );

    expect($advanced->current_step)->toBe(OnboardingStep::PrivacyDiscovery)
        ->and($advanced->pet_relationship_choice)->toBe(OnboardingPetChoice::AccessRequested)
        ->and(PetProfileManager::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('foreign or mismatched access requests do not resolve care intent', function (string $case): void {
    $user = User::factory()->onboardingAtPets()->create();
    $otherUser = User::factory()->create();
    $profile = PetProfile::factory()->discoverable()->create();

    if ($case === 'foreign pending') {
        PetProfileAccessRequest::factory()
            ->for($profile, 'profile')
            ->for($otherUser, 'requester')
            ->create();
    } elseif ($case === 'inactive pending') {
        PetProfileAccessRequest::factory()
            ->for($profile, 'profile')
            ->for($user, 'requester')
            ->create(['active_key' => null]);
    } else {
        $otherProfile = PetProfile::factory()->discoverable()->create();
        $manager = PetProfileManager::factory()
            ->for($otherProfile, 'profile')
            ->for($user)
            ->create();
        PetProfileAccessRequest::factory()
            ->for($profile, 'profile')
            ->for($user, 'requester')
            ->create([
                'status' => PetProfileAccessRequestStatus::Approved,
                'active_key' => null,
                'granted_manager_id' => $manager->id,
            ]);
    }

    $state = $user->onboarding()->firstOrFail();
    $this->actingAs($user);

    expect(fn () => app(AdvanceUserOnboarding::class)->handle(
        $user,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::AccessRequested,
    ))->toThrow(ValidationException::class);
})->with(['foreign pending', 'inactive pending', 'mismatched approved manager']);

test('an approved current invitation is request progress but not active management', function (): void {
    $user = User::factory()->onboardingAtPets()->create();
    $profile = PetProfile::factory()->discoverable()->create();
    $manager = PetProfileManager::factory()
        ->for($profile, 'profile')
        ->for($user)
        ->invited()
        ->create();
    PetProfileAccessRequest::factory()
        ->for($profile, 'profile')
        ->for($user, 'requester')
        ->create([
            'status' => PetProfileAccessRequestStatus::Approved,
            'active_key' => null,
            'granted_manager_id' => $manager->id,
        ]);
    $evidence = app(OnboardingPetEvidence::class);

    expect($evidence->supports($user, OnboardingPetChoice::AccessRequested))->toBeTrue()
        ->and($evidence->supports($user, OnboardingPetChoice::ManagedPet))->toBeFalse();
});

test('soft deleted pets and revoked creator relationships do not resolve management', function (
    string $case,
): void {
    $user = User::factory()->onboardingAtPets()->create();
    $profile = PetProfile::factory()->for($user)->privateProfile()->create();

    if ($case === 'soft deleted') {
        $profile->deleteOrFail();
    } else {
        PetProfileManager::factory()
            ->for($profile, 'profile')
            ->for($user)
            ->create([
                'status' => PetManagerStatus::Revoked,
                'revoked_at' => now(),
            ]);
    }

    $state = $user->onboarding()->firstOrFail();
    $this->actingAs($user);

    expect(fn () => app(AdvanceUserOnboarding::class)->handle(
        $user,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::ManagedPet,
    ))->toThrow(ValidationException::class);
})->with(['soft deleted', 'revoked creator manager']);

test('the pet step renders four semantic localized choices in every locale', function (string $locale): void {
    $user = User::factory()->onboardingAtPets()->create(['locale' => $locale]);

    $response = $this->actingAs($user)->get(route('onboarding.show'))->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->query('//fieldset[@data-onboarding-pet-choices]'))->toHaveCount(1)
        ->and($xpath->query('//fieldset[@data-onboarding-pet-choices]//input[@type="radio"]'))->toHaveCount(4)
        ->and($xpath->query('//input[@value="managed-pet"]'))->toHaveCount(1)
        ->and($xpath->query('//input[@value="access-requested"]'))->toHaveCount(1)
        ->and($xpath->query('//input[@value="no-pet"]'))->toHaveCount(1)
        ->and($xpath->query('//input[@value="add-later"]'))->toHaveCount(1);

    $response
        ->assertSee(trans('onboarding.steps.pet_relationship.no_pet.label', locale: $locale))
        ->assertSee(trans('onboarding.steps.pet_relationship.add_later.label', locale: $locale))
        ->assertDontSee('onboarding.');
})->with(['en', 'lt', 'ru']);

test('managed pet summaries are permission safe and bounded', function (): void {
    $user = User::factory()->onboardingAtPets()->create();

    PetProfile::factory()
        ->privateProfile()
        ->count(105)
        ->sequence(fn (Sequence $sequence): array => [
            'name' => 'Visible pet '.($sequence->index + 1),
        ])
        ->create()
        ->each(function (PetProfile $profile) use ($user): void {
            PetProfileManager::factory()->for($profile, 'profile')->for($user)->create();
        });

    $hidden = PetProfile::factory()->privateProfile()->create([
        'name' => 'Permission-hidden pet',
    ]);
    PetProfileManager::factory()->for($hidden, 'profile')->for($user)->create([
        'permission_overrides' => ['deny' => [PetProfilePermission::View->value]],
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $response = $this->actingAs($user)->get(route('onboarding.show'))->assertOk();
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();
    $xpath = responseXPath($response);

    expect($xpath->query('//*[@data-onboarding-managed-pet]'))->toHaveCount(5)
        ->and($queryCount)->toBeLessThanOrEqual(12);

    $response
        ->assertSee(__('onboarding.steps.pet_relationship.managed_summary_more'))
        ->assertDontSee('Permission-hidden pet');
});

test('privacy can return to the pet decision without deleting domain data', function (): void {
    $user = User::factory()->onboardingAtPrivacy()->create();
    $state = $user->onboarding()->firstOrFail();
    $profile = PetProfile::factory()->for($user)->privateProfile()->create();

    Livewire::actingAs($user)
        ->test(Onboarding::class)
        ->call('editPetRelationship')
        ->assertHasNoErrors()
        ->assertSet('expectedStep', OnboardingStep::PetRelationship->value)
        ->set('petForm.choice', OnboardingPetChoice::AddLater->value)
        ->call('savePetRelationship')
        ->assertHasNoErrors()
        ->assertSet('expectedStep', OnboardingStep::PrivacyDiscovery->value);

    expect($state->fresh()?->pet_relationship_choice)->toBe(OnboardingPetChoice::AddLater)
        ->and($profile->fresh())->not->toBeNull();
});

test('the pet revisit action is owner scoped stale guarded and idempotent', function (): void {
    $user = User::factory()->onboardingAtPrivacy()->create();
    $otherUser = User::factory()->onboardingAtPrivacy()->create();
    $state = $user->onboarding()->firstOrFail();
    $this->actingAs($user);
    $action = app(RevisitOnboardingPetRelationship::class);

    $revisited = $action->handle($user, $state->lock_version);
    $replayed = $action->handle($user, $state->lock_version);

    expect($revisited->current_step)->toBe(OnboardingStep::PetRelationship)
        ->and($replayed->lock_version)->toBe($revisited->lock_version)
        ->and($revisited->pet_relationship_choice)->toBe(OnboardingPetChoice::AddLater)
        ->and($revisited->pet_relationship_completed_at)->not->toBeNull();

    expect(fn () => $action->handle($user, 1))->toThrow(ValidationException::class)
        ->and(fn () => $action->handle($otherUser, 4))->toThrow(HttpException::class);

    expect($otherUser->onboarding()->firstOrFail()->current_step)
        ->toBe(OnboardingStep::PrivacyDiscovery);
});

test('onboarding pet creation uses canonical private defaults and a server-only return', function (): void {
    $user = User::factory()->onboardingAtPets()->create();

    Livewire::withQueryParams([
        'return_url' => 'https://evil.example',
        'next' => '//evil.example',
    ])->actingAs($user)
        ->test(CreatePetProfile::class)
        ->set('form.name', 'Private onboarding pet')
        ->set('form.species', 'dog')
        ->set('form.relationshipRole', 'primary-owner')
        ->set('form.visibility', 'private')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.show'));

    $profile = PetProfile::query()->whereBelongsTo($user)->sole();
    $privacy = PetProfilePrivacySetting::query()->whereBelongsTo($profile, 'profile')->sole();
    $manager = PetProfileManager::query()
        ->whereBelongsTo($profile, 'profile')
        ->whereBelongsTo($user)
        ->sole();

    expect($profile->visibility)->toBe(PetProfileVisibility::Private->value)
        ->and($profile->status)->toBe(PetProfileStatus::Draft)
        ->and($profile->is_discoverable)->toBeFalse()
        ->and($profile->allow_external_indexing)->toBeFalse()
        ->and($privacy->profile_visibility)->toBe(PetProfileVisibility::Private)
        ->and($privacy->section_rules)->toBe([])
        ->and($privacy->is_discoverable)->toBeFalse()
        ->and($privacy->allow_external_indexing)->toBeFalse()
        ->and($privacy->allow_direct_link)->toBeFalse()
        ->and($privacy->owner_display_mode)->toBe('contact-button')
        ->and($privacy->manager_display_mode)->toBe('hidden')
        ->and($privacy->public_location_precision)->toBe('hidden')
        ->and($manager->status)->toBe(PetManagerStatus::Active)
        ->and($manager->role)->toBe(PetManagerRole::PrimaryOwner)
        ->and(PetProfileSlugAlias::query()->whereBelongsTo($profile, 'profile')->count())->toBe(1)
        ->and(PetProfileLifecycleEvent::query()->whereBelongsTo($profile, 'profile')->count())->toBe(1)
        ->and(AuditLog::query()
            ->where('action', 'pet-profile.created')
            ->where('target_id', (string) $profile->id)
            ->count())->toBe(1);
});

test('normal pet creation keeps its canonical workspace destination', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(CreatePetProfile::class)
        ->set('form.name', 'Normal creation pet')
        ->set('form.species', 'dog')
        ->set('form.relationshipRole', 'primary-owner')
        ->set('form.visibility', 'private')
        ->call('create')
        ->assertHasNoErrors();

    $profile = PetProfile::query()->whereBelongsTo($user)->sole();

    $component->assertRedirect(route('pets.manage.show', ['petProfile' => $profile->profile_key]));
});

test('a stale pet component cannot regress a newer privacy state', function (): void {
    $user = User::factory()->onboardingAtPets()->create();
    $component = Livewire::actingAs($user)->test(Onboarding::class)
        ->set('petForm.choice', OnboardingPetChoice::NoPet->value);
    $state = $user->onboarding()->firstOrFail();
    $state->forceFill([
        'current_step' => OnboardingStep::PrivacyDiscovery,
        'pet_relationship_choice' => OnboardingPetChoice::AddLater,
        'pet_relationship_completed_at' => now(),
        'lock_version' => 4,
    ])->saveOrFail();

    $component
        ->call('savePetRelationship')
        ->assertHasNoErrors()
        ->assertRedirect(route('onboarding.show'));

    expect($state->fresh()?->pet_relationship_choice)->toBe(OnboardingPetChoice::AddLater)
        ->and($state->fresh()?->lock_version)->toBe(4);
});
