<?php

declare(strict_types=1);

use App\Actions\AdvanceUserOnboarding;
use App\Actions\CompleteOnboardingPreferences;
use App\Actions\CompleteOnboardingPrivacy;
use App\Actions\DeferOnboardingPetRelationship;
use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\PetProfileAccessRequestStatus;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\SocialActorResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('it rejects completion when a persisted mandatory checkpoint is missing', function (string $attribute): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->privacyDiscovery()
        ->create();
    $state->forceFill([$attribute => null])->saveOrFail();
    $actor = app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);
    $settings = $actor->settings()->firstOrFail();

    expect(fn () => app(CompleteOnboardingPrivacy::class)->handle(
        user: $this->authenticatedUser,
        privacyAcknowledged: true,
        isDiscoverable: true,
        isRecommendable: true,
        allowMessageRequests: true,
        expectedStep: OnboardingStep::PrivacyDiscovery,
        expectedOnboardingLockVersion: $state->lock_version,
        expectedSocialSettingsLockVersion: $settings->lock_version,
    ))->toThrow(ValidationException::class);

    expect($state->fresh()?->completed_at)->toBeNull()
        ->and($actor->fresh()?->is_discoverable)->toBeFalse();
})->with([
    'introduction checkpoint' => ['introduction_completed_at'],
    'preferences checkpoint' => ['preferences_completed_at'],
    'pet choice' => ['pet_relationship_choice'],
    'pet checkpoint' => ['pet_relationship_completed_at'],
]);

test('it rechecks managed pet evidence immediately before completion', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->petRelationship()
        ->create();
    $pet = PetProfile::factory()->for($this->authenticatedUser)->privateProfile()->create();
    app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::ManagedPet,
    );
    PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($this->authenticatedUser)
        ->create([
            'role' => PetManagerRole::PrimaryOwner,
            'status' => PetManagerStatus::Revoked,
            'revoked_at' => now(),
        ]);
    $actor = app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);
    $settings = $actor->settings()->firstOrFail();
    $state->refresh();

    expect(fn () => app(CompleteOnboardingPrivacy::class)->handle(
        user: $this->authenticatedUser,
        privacyAcknowledged: true,
        isDiscoverable: false,
        isRecommendable: false,
        allowMessageRequests: false,
        expectedStep: OnboardingStep::PrivacyDiscovery,
        expectedOnboardingLockVersion: $state->lock_version,
        expectedSocialSettingsLockVersion: $settings->lock_version,
    ))->toThrow(ValidationException::class);

    expect($state->fresh()?->completed_at)->toBeNull();

    $recovered = app(DeferOnboardingPetRelationship::class)->handle(
        $this->authenticatedUser,
        $state->refresh()->lock_version,
    );
    $replayed = app(DeferOnboardingPetRelationship::class)->handle(
        $this->authenticatedUser,
        $state->lock_version,
    );

    expect($recovered->current_step)->toBe(OnboardingStep::PrivacyDiscovery)
        ->and($recovered->pet_relationship_choice)->toBe(OnboardingPetChoice::NotNow)
        ->and($recovered->lock_version)->toBe(5)
        ->and($replayed->lock_version)->toBe(5);

    expect(fn () => app(DeferOnboardingPetRelationship::class)->handle(
        $this->authenticatedUser,
        1,
    ))->toThrow(ValidationException::class);

    $completed = app(CompleteOnboardingPrivacy::class)->handle(
        user: $this->authenticatedUser,
        privacyAcknowledged: true,
        isDiscoverable: false,
        isRecommendable: false,
        allowMessageRequests: false,
        expectedStep: OnboardingStep::PrivacyDiscovery,
        expectedOnboardingLockVersion: $recovered->lock_version,
        expectedSocialSettingsLockVersion: $settings->lock_version,
    );

    expect($completed->isComplete())->toBeTrue();
});

test('it rejects completion after the supporting pet profile is deleted', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->petRelationship()
        ->create();
    $pet = PetProfile::factory()->for($this->authenticatedUser)->privateProfile()->create();
    PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($this->authenticatedUser)
        ->create();
    app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::ManagedPet,
    );
    $pet->delete();
    $actor = app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);
    $settings = $actor->settings()->firstOrFail();
    $state->refresh();

    expect(fn () => app(CompleteOnboardingPrivacy::class)->handle(
        user: $this->authenticatedUser,
        privacyAcknowledged: true,
        isDiscoverable: false,
        isRecommendable: false,
        allowMessageRequests: false,
        expectedStep: OnboardingStep::PrivacyDiscovery,
        expectedOnboardingLockVersion: $state->lock_version,
        expectedSocialSettingsLockVersion: $settings->lock_version,
    ))->toThrow(ValidationException::class);

    expect($state->fresh()?->completed_at)->toBeNull();
});

test('it rejects non-equivalent pet replay while preserving the newer state', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->petRelationship()
        ->create();
    $advanced = app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::NotNow,
    );

    $replayed = app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::NotNow,
    );

    expect($replayed->id)->toBe($advanced->id)
        ->and($replayed->current_step)->toBe(OnboardingStep::PrivacyDiscovery)
        ->and($replayed->lock_version)->toBe(4);

    expect(fn () => app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::PetRelationship,
        $state->lock_version,
    ))->toThrow(ValidationException::class);

    expect($state->fresh()?->current_step)->toBe(OnboardingStep::PrivacyDiscovery)
        ->and($state->fresh()?->pet_relationship_choice)->toBe(OnboardingPetChoice::NotNow)
        ->and($state->fresh()?->lock_version)->toBe(4);
});

test('it only accepts equivalent introduction and preference replays', function (): void {
    $introduction = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->create();
    app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::Introduction,
        $introduction->lock_version,
        introductionAcknowledged: true,
    );

    expect(fn () => app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::Introduction,
        $introduction->lock_version,
        introductionAcknowledged: false,
    ))->toThrow(ValidationException::class);

    $this->authenticatedUser->onboarding()->delete();
    $preferences = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->preferences()
        ->create();
    $staleUserSnapshot = $this->authenticatedUser->fresh();
    app(CompleteOnboardingPreferences::class)->handle(
        $this->authenticatedUser,
        ['locale' => 'lt', 'timezone' => 'Europe/Vilnius'],
        OnboardingStep::Preferences,
        $preferences->lock_version,
    );

    $replayed = app(CompleteOnboardingPreferences::class)->handle(
        $staleUserSnapshot,
        ['locale' => 'lt', 'timezone' => 'Europe/Vilnius'],
        OnboardingStep::Preferences,
        $preferences->lock_version,
    );

    expect($replayed->current_step)->toBe(OnboardingStep::PetRelationship);

    expect(fn () => app(CompleteOnboardingPreferences::class)->handle(
        $staleUserSnapshot,
        ['locale' => 'en', 'timezone' => 'Europe/Vilnius'],
        OnboardingStep::Preferences,
        $preferences->lock_version,
    ))->toThrow(ValidationException::class);

    expect($this->authenticatedUser->fresh())
        ->locale->toBe('lt')
        ->timezone->toBe('Europe/Vilnius');
});

test('it rejects expired access requests and accepts an approved active grant', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->petRelationship()
        ->create();
    $profile = PetProfile::factory()->discoverable()->create();
    PetProfileAccessRequest::factory()
        ->for($profile, 'profile')
        ->for($this->authenticatedUser, 'requester')
        ->create(['temporary_access_ends_at' => now()->subMinute()]);

    expect(fn () => app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::AccessRequested,
    ))->toThrow(ValidationException::class);

    $manager = PetProfileManager::factory()
        ->for($profile, 'profile')
        ->for($this->authenticatedUser)
        ->create();
    PetProfileAccessRequest::factory()
        ->for($profile, 'profile')
        ->for($this->authenticatedUser, 'requester')
        ->create([
            'status' => PetProfileAccessRequestStatus::Approved,
            'active_key' => null,
            'granted_manager_id' => $manager->id,
            'reviewed_at' => now(),
        ]);

    $advanced = app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::AccessRequested,
    );

    expect($advanced->current_step)->toBe(OnboardingStep::PrivacyDiscovery)
        ->and($advanced->pet_relationship_choice)->toBe(OnboardingPetChoice::AccessRequested);
});

test('it rejects an inactive access request lifecycle status', function (PetProfileAccessRequestStatus $status): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->petRelationship()
        ->create();
    PetProfileAccessRequest::factory()
        ->for($this->authenticatedUser, 'requester')
        ->create([
            'status' => $status,
            'active_key' => null,
            'reviewed_at' => now(),
        ]);

    expect(fn () => app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::PetRelationship,
        $state->lock_version,
        OnboardingPetChoice::AccessRequested,
    ))->toThrow(ValidationException::class);

    expect($state->fresh()?->current_step)->toBe(OnboardingStep::PetRelationship)
        ->and($state->fresh()?->pet_relationship_choice)->toBeNull();
})->with([
    PetProfileAccessRequestStatus::Rejected,
    PetProfileAccessRequestStatus::Cancelled,
    PetProfileAccessRequestStatus::Expired,
]);

test('it recovers a malformed persisted step only through the first guarded transition', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->create();
    DB::table('user_onboardings')
        ->where('id', $state->id)
        ->update([
            'current_step' => 'forged-complete',
            'pet_relationship_choice' => 'forged-choice',
        ]);

    $recovered = app(AdvanceUserOnboarding::class)->handle(
        $this->authenticatedUser,
        OnboardingStep::Introduction,
        $state->lock_version,
        introductionAcknowledged: true,
    );

    expect($recovered->current_step)->toBe(OnboardingStep::Preferences)
        ->and($recovered->introduction_completed_at)->not->toBeNull()
        ->and($recovered->completed_at)->toBeNull()
        ->and($recovered->lock_version)->toBe(2);
});

test('it rejects cross-account and wrong-operation direct action calls', function (): void {
    $foreignIntroduction = User::factory()->onboardingIncomplete()->create();
    $foreignIntroductionState = $foreignIntroduction->onboarding()->firstOrFail();
    $other = User::factory()->onboardingAtPreferences()->create();
    $otherState = $other->onboarding()->firstOrFail();
    $foreignPrivacy = User::factory()->onboardingAtPrivacy()->create();
    $foreignPrivacyState = $foreignPrivacy->onboarding()->firstOrFail();
    $foreignPrivacyActor = $foreignPrivacy->socialActor()->firstOrFail();
    $foreignPrivacySettings = $foreignPrivacyActor->settings()->firstOrFail();

    $foreignOperations = [
        fn () => app(AdvanceUserOnboarding::class)->handle(
            $foreignIntroduction,
            OnboardingStep::Introduction,
            $foreignIntroductionState->lock_version,
            introductionAcknowledged: true,
        ),
        fn () => app(CompleteOnboardingPreferences::class)->handle(
            $other,
            ['locale' => 'ru', 'timezone' => 'Europe/Riga'],
            OnboardingStep::Preferences,
            $otherState->lock_version,
        ),
        fn () => app(CompleteOnboardingPrivacy::class)->handle(
            user: $foreignPrivacy,
            privacyAcknowledged: true,
            isDiscoverable: true,
            isRecommendable: true,
            allowMessageRequests: true,
            expectedStep: OnboardingStep::PrivacyDiscovery,
            expectedOnboardingLockVersion: $foreignPrivacyState->lock_version,
            expectedSocialSettingsLockVersion: $foreignPrivacySettings->lock_version,
        ),
        fn () => app(DeferOnboardingPetRelationship::class)->handle(
            $foreignPrivacy,
            $foreignPrivacyState->lock_version,
        ),
    ];

    foreach ($foreignOperations as $operation) {
        try {
            $operation();
            $this->fail('A foreign onboarding Action unexpectedly succeeded.');
        } catch (HttpException $exception) {
            expect($exception->getStatusCode())->toBe(403);
        }
    }

    expect($foreignIntroductionState->fresh()?->current_step)->toBe(OnboardingStep::Introduction)
        ->and($otherState->fresh()?->current_step)->toBe(OnboardingStep::Preferences)
        ->and($foreignPrivacyState->fresh()?->current_step)->toBe(OnboardingStep::PrivacyDiscovery)
        ->and($foreignPrivacyActor->fresh()?->is_discoverable)->toBeFalse()
        ->and($foreignPrivacySettings->fresh()?->is_recommendable)->toBeFalse();

    $completed = User::factory()->onboarded()->create();
    $completedState = $completed->onboarding()->firstOrFail();
    $actor = $completed->socialActor()->firstOrFail();
    $settings = $actor->settings()->firstOrFail();
    $this->actingAs($completed);

    expect(fn () => app(CompleteOnboardingPrivacy::class)->handle(
        user: $completed,
        privacyAcknowledged: true,
        isDiscoverable: true,
        isRecommendable: true,
        allowMessageRequests: true,
        expectedStep: OnboardingStep::Preferences,
        expectedOnboardingLockVersion: $completedState->lock_version,
        expectedSocialSettingsLockVersion: $settings->lock_version,
    ))->toThrow(ValidationException::class);
});

test('pet relationship deferral rejects unavailable accounts without mutation', function (string $status): void {
    $user = User::factory()
        ->onboardingAtPrivacy()
        ->{$status}()
        ->create();
    $state = $user->onboarding()->firstOrFail();
    $state->forceFill(['pet_relationship_choice' => OnboardingPetChoice::ManagedPet])->saveOrFail();
    $this->actingAs($user);

    try {
        app(DeferOnboardingPetRelationship::class)->handle($user, $state->lock_version);
        $this->fail('An unavailable account unexpectedly changed its pet choice.');
    } catch (HttpException $exception) {
        expect($exception->getStatusCode())->toBe(403);
    }

    expect($state->fresh()?->pet_relationship_choice)->toBe(OnboardingPetChoice::ManagedPet)
        ->and($state->fresh()?->lock_version)->toBe(4);
})->with(['blocked', 'suspended']);

test('pet relationship deferral rejects every non-privacy state without mutation', function (string $factoryState): void {
    $user = User::factory()->{$factoryState}()->create();
    $state = $user->onboarding()->firstOrFail();
    $choice = $state->pet_relationship_choice;
    $version = $state->lock_version;
    $this->actingAs($user);

    expect(fn () => app(DeferOnboardingPetRelationship::class)->handle(
        $user,
        $version,
    ))->toThrow(ValidationException::class);

    expect($state->fresh()?->pet_relationship_choice)->toBe($choice)
        ->and($state->fresh()?->lock_version)->toBe($version);
})->with([
    'introduction' => 'onboardingIncomplete',
    'preferences' => 'onboardingAtPreferences',
    'pet relationship' => 'onboardingAtPets',
    'complete' => 'onboarded',
]);

test('it preserves the original completion timestamp and settings on exact replay', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->privacyDiscovery()
        ->create();
    $actor = app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);
    $settings = $actor->settings()->firstOrFail();
    $this->freezeTime();
    $completed = app(CompleteOnboardingPrivacy::class)->handle(
        user: $this->authenticatedUser,
        privacyAcknowledged: true,
        isDiscoverable: true,
        isRecommendable: false,
        allowMessageRequests: true,
        expectedStep: OnboardingStep::PrivacyDiscovery,
        expectedOnboardingLockVersion: $state->lock_version,
        expectedSocialSettingsLockVersion: $settings->lock_version,
    );
    $completedAt = $completed->completed_at;
    $completedVersion = $completed->lock_version;
    $settingsVersion = $settings->fresh()?->lock_version;
    $this->travel(5)->minutes();

    $replayed = app(CompleteOnboardingPrivacy::class)->handle(
        user: $this->authenticatedUser,
        privacyAcknowledged: true,
        isDiscoverable: true,
        isRecommendable: false,
        allowMessageRequests: true,
        expectedStep: OnboardingStep::PrivacyDiscovery,
        expectedOnboardingLockVersion: $state->lock_version,
        expectedSocialSettingsLockVersion: $settings->lock_version,
    );

    expect($replayed->completed_at?->equalTo($completedAt))->toBeTrue()
        ->and($replayed->lock_version)->toBe($completedVersion)
        ->and($actor->fresh()?->is_discoverable)->toBeTrue()
        ->and($settings->fresh()?->is_recommendable)->toBeFalse()
        ->and($settings->fresh()?->allow_message_requests)->toBeTrue()
        ->and($settings->fresh()?->lock_version)->toBe($settingsVersion);
});

test('completed privacy replay fails closed when canonical social identity is missing', function (): void {
    $state = UserOnboarding::factory()->completed()->create();
    $user = $state->user;
    $this->actingAs($user);

    expect($user->socialActor()->exists())->toBeFalse();

    expect(fn () => app(CompleteOnboardingPrivacy::class)->handle(
        user: $user,
        privacyAcknowledged: true,
        isDiscoverable: true,
        isRecommendable: true,
        allowMessageRequests: true,
        expectedStep: OnboardingStep::PrivacyDiscovery,
        expectedOnboardingLockVersion: 4,
        expectedSocialSettingsLockVersion: 0,
    ))->toThrow(ValidationException::class);

    expect($user->socialActor()->exists())->toBeFalse()
        ->and($state->fresh()?->completed_at?->equalTo($state->completed_at))->toBeTrue()
        ->and($state->fresh()?->lock_version)->toBe(5);
});

test('it rejects a changed privacy payload replay without mutating completion', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->privacyDiscovery()
        ->create();
    $actor = app(SocialActorResolver::class)->provisionPrivateForUser($this->authenticatedUser);
    $settings = $actor->settings()->firstOrFail();
    $completed = app(CompleteOnboardingPrivacy::class)->handle(
        user: $this->authenticatedUser,
        privacyAcknowledged: true,
        isDiscoverable: true,
        isRecommendable: false,
        allowMessageRequests: true,
        expectedStep: OnboardingStep::PrivacyDiscovery,
        expectedOnboardingLockVersion: $state->lock_version,
        expectedSocialSettingsLockVersion: $settings->lock_version,
    );
    $completedAt = $completed->completed_at;

    expect(fn () => app(CompleteOnboardingPrivacy::class)->handle(
        user: $this->authenticatedUser,
        privacyAcknowledged: true,
        isDiscoverable: false,
        isRecommendable: true,
        allowMessageRequests: false,
        expectedStep: OnboardingStep::PrivacyDiscovery,
        expectedOnboardingLockVersion: $state->lock_version,
        expectedSocialSettingsLockVersion: $settings->lock_version,
    ))->toThrow(ValidationException::class);

    expect($state->fresh()?->completed_at?->equalTo($completedAt))->toBeTrue()
        ->and($actor->fresh()?->is_discoverable)->toBeTrue()
        ->and($settings->fresh()?->is_recommendable)->toBeFalse()
        ->and($settings->fresh()?->allow_message_requests)->toBeTrue();
});
