<?php

declare(strict_types=1);

use App\Actions\AdvanceUserOnboarding;
use App\Actions\InitializeUserOnboarding;
use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\SocialActorResolver;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('it exposes the canonical additive onboarding schema', function (): void {
    expect(Schema::hasTable('user_onboardings'))->toBeTrue()
        ->and(Schema::hasColumns('user_onboardings', [
            'id',
            'user_id',
            'current_step',
            'pet_relationship_choice',
            'started_at',
            'introduction_completed_at',
            'preferences_completed_at',
            'pet_relationship_completed_at',
            'privacy_discovery_completed_at',
            'completed_at',
            'lock_version',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

test('it rejects mass assignment of authoritative onboarding state', function (): void {
    $state = UserOnboarding::factory()
        ->for($this->authenticatedUser)
        ->create();

    expect(fn () => $state->fill([
        'current_step' => OnboardingStep::Complete,
        'completed_at' => now(),
        'lock_version' => 999,
    ]))->toThrow(MassAssignmentException::class);

    expect($state->fresh()?->current_step)->toBe(OnboardingStep::Introduction)
        ->and($state->fresh()?->completed_at)->toBeNull()
        ->and($state->fresh()?->lock_version)->toBe(1);
});

test('it preserves legacy onboarding compatibility without changing account lifecycle facts', function (): void {
    $active = User::factory()->create();
    $unverified = User::factory()->unverified()->create();
    $administrator = User::factory()->administrator()->create();
    $blocked = User::factory()->blocked()->create();
    $suspended = User::factory()->suspended()->create();

    expect($active->hasCompletedOnboarding())->toBeTrue()
        ->and($active->requiresOnboarding())->toBeFalse()
        ->and($unverified->hasCompletedOnboarding())->toBeTrue()
        ->and($unverified->hasVerifiedEmail())->toBeFalse()
        ->and($administrator->hasCompletedOnboarding())->toBeTrue()
        ->and($administrator->isAdministrator())->toBeTrue()
        ->and($blocked->hasCompletedOnboarding())->toBeTrue()
        ->and($blocked->status)->toBe(UserStatus::Blocked)
        ->and($suspended->hasCompletedOnboarding())->toBeTrue()
        ->and($suspended->status)->toBe(UserStatus::Suspended);
});

test('it exposes internally consistent named factory states while preserving the legacy default', function (): void {
    $legacy = User::factory()->create();
    $introduction = User::factory()->onboardingIncomplete()->create()->onboarding()->firstOrFail();
    $preferences = User::factory()->onboardingAtPreferences()->create()->onboarding()->firstOrFail();
    $pets = User::factory()->onboardingAtPets()->create()->onboarding()->firstOrFail();
    $privacy = User::factory()->onboardingAtPrivacy()->create()->onboarding()->firstOrFail();
    $complete = User::factory()->onboarded()->create()->onboarding()->firstOrFail();

    expect($legacy->hasCompletedOnboarding())->toBeTrue()
        ->and($introduction->current_step)->toBe(OnboardingStep::Introduction)
        ->and($introduction->completed_at)->toBeNull()
        ->and($introduction->lock_version)->toBe(1)
        ->and($introduction->introduction_completed_at)->toBeNull()
        ->and($preferences->current_step)->toBe(OnboardingStep::Preferences)
        ->and($preferences->introduction_completed_at)->not->toBeNull()
        ->and($preferences->preferences_completed_at)->toBeNull()
        ->and($preferences->lock_version)->toBe(2)
        ->and($pets->current_step)->toBe(OnboardingStep::PetRelationship)
        ->and($pets->preferences_completed_at)->not->toBeNull()
        ->and($pets->pet_relationship_completed_at)->toBeNull()
        ->and($pets->lock_version)->toBe(3)
        ->and($privacy->current_step)->toBe(OnboardingStep::PrivacyDiscovery)
        ->and($privacy->pet_relationship_choice)->toBe(OnboardingPetChoice::AddLater)
        ->and($privacy->pet_relationship_completed_at)->not->toBeNull()
        ->and($privacy->privacy_discovery_completed_at)->toBeNull()
        ->and($privacy->lock_version)->toBe(4)
        ->and($complete->isComplete())->toBeTrue()
        ->and($complete->privacy_discovery_completed_at)->not->toBeNull()
        ->and($complete->completed_at)->not->toBeNull()
        ->and($complete->lock_version)->toBe(5);
});

test('it initializes onboarding once without resetting progress or timestamps', function (): void {
    $user = User::factory()->create();
    $this->freezeTime();
    $first = app(InitializeUserOnboarding::class)->handle($user);
    $startedAt = $first->started_at;
    $first->forceFill([
        'current_step' => OnboardingStep::Preferences,
        'introduction_completed_at' => now(),
        'lock_version' => 2,
    ])->saveOrFail();
    $this->travel(5)->minutes();

    $replayed = app(InitializeUserOnboarding::class)->handle($user);

    expect($replayed->is($first))->toBeTrue()
        ->and($replayed->current_step)->toBe(OnboardingStep::Preferences)
        ->and($replayed->started_at->equalTo($startedAt))->toBeTrue()
        ->and($replayed->lock_version)->toBe(2)
        ->and($user->onboarding()->count())->toBe(1)
        ->and($user->socialActor()->count())->toBe(1)
        ->and($user->socialActor()->firstOrFail()->settings()->count())->toBe(1);
});

test('it fails closed without a server error when stored enums are malformed', function (): void {
    $user = User::factory()->create();
    app(SocialActorResolver::class)->provisionPrivateForUser($user);
    DB::table('user_onboardings')->insert([
        'user_id' => $user->id,
        'current_step' => 'forged-complete',
        'pet_relationship_choice' => 'forged-choice',
        'started_at' => now(),
        'lock_version' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $this->actingAs($user);

    $this->get(route('content.index'))->assertRedirect(route('onboarding.show'));
    $this->get(route('onboarding.show'))
        ->assertOk()
        ->assertSee('aria-current="step"', false)
        ->assertSee(__('onboarding.steps.introduction.title'));

    expect($user->fresh()?->hasCompletedOnboarding())->toBeFalse();
});

test('it never treats an under-versioned completion as completed onboarding', function (): void {
    $user = User::factory()->onboarded()->create();
    DB::table('user_onboardings')
        ->where('user_id', $user->id)
        ->update(['lock_version' => 4]);

    $this->actingAs($user)
        ->get(route('content.index'))
        ->assertRedirect(route('onboarding.show'));

    expect($user->fresh()?->hasCompletedOnboarding())->toBeFalse();
});

test('it never treats an internally incomplete complete row as completed onboarding', function (): void {
    $user = User::factory()->onboarded()->create();
    DB::table('user_onboardings')
        ->where('user_id', $user->id)
        ->update(['preferences_completed_at' => null]);

    $this->actingAs($user)
        ->get(route('content.index'))
        ->assertRedirect(route('onboarding.show'));

    expect($user->fresh()?->hasCompletedOnboarding())->toBeFalse();
});

test('it safely resumes and repairs a complete row with malformed started time', function (): void {
    $user = User::factory()->onboarded()->create();
    DB::table('user_onboardings')
        ->where('user_id', $user->id)
        ->update(['started_at' => 'not-a-timestamp']);
    $this->actingAs($user);

    $this->get(route('content.index'))->assertRedirect(route('onboarding.show'));
    $this->get(route('onboarding.show'))
        ->assertSuccessful()
        ->assertSee(__('onboarding.steps.introduction.title'));

    $state = $user->onboarding()->firstOrFail();
    $repaired = app(AdvanceUserOnboarding::class)->handle(
        $user,
        OnboardingStep::Introduction,
        $state->lock_version,
        introductionAcknowledged: true,
    );
    $replayed = app(AdvanceUserOnboarding::class)->handle(
        $user,
        OnboardingStep::Introduction,
        $state->lock_version,
        introductionAcknowledged: true,
    );

    expect($repaired->current_step)->toBe(OnboardingStep::Preferences)
        ->and($repaired->hasPersistedTimestamp('started_at'))->toBeTrue()
        ->and($repaired->preferences_completed_at)->toBeNull()
        ->and($repaired->pet_relationship_choice)->toBeNull()
        ->and($repaired->pet_relationship_completed_at)->toBeNull()
        ->and($repaired->privacy_discovery_completed_at)->toBeNull()
        ->and($repaired->completed_at)->toBeNull()
        ->and($repaired->lock_version)->toBe(6)
        ->and($repaired->isComplete())->toBeFalse()
        ->and($replayed->lock_version)->toBe(6);
});
