<?php

declare(strict_types=1);

use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Models\UserOnboarding;
use App\Services\OnboardingState;

/** @param array<string, mixed> $attributes */
function onboardingStateModel(array $attributes): UserOnboarding
{
    $state = new UserOnboarding;
    $state->setRawAttributes(array_merge([
        'current_step' => OnboardingStep::Introduction->value,
        'pet_relationship_choice' => null,
        'started_at' => '2026-08-30 10:00:00',
        'introduction_completed_at' => null,
        'preferences_completed_at' => null,
        'pet_relationship_completed_at' => null,
        'privacy_discovery_completed_at' => null,
        'completed_at' => null,
        'lock_version' => 1,
    ], $attributes), true);

    return $state;
}

test('it reports canonical traversal without permitting browser step selection', function (): void {
    $resolver = new OnboardingState;
    $state = onboardingStateModel([
        'current_step' => OnboardingStep::PetRelationship->value,
        'introduction_completed_at' => '2026-08-30 10:01:00',
        'preferences_completed_at' => '2026-08-30 10:02:00',
        'lock_version' => 3,
    ]);

    expect($resolver->currentStep($state))->toBe(OnboardingStep::PetRelationship)
        ->and($resolver->nextStep($state))->toBe(OnboardingStep::PrivacyDiscovery)
        ->and($resolver->previousStep($state))->toBe(OnboardingStep::Preferences)
        ->and($resolver->canEnter($state, OnboardingStep::PetRelationship))->toBeTrue()
        ->and($resolver->canEnter($state, OnboardingStep::Preferences))->toBeFalse()
        ->and($resolver->canEnter($state, OnboardingStep::PrivacyDiscovery))->toBeFalse();
});

test('it normalizes malformed or internally inconsistent persistence to the earliest safe step', function (): void {
    $resolver = new OnboardingState;
    $unknown = onboardingStateModel([
        'current_step' => 'forged-complete',
        'completed_at' => '2026-08-30 10:05:00',
    ]);
    $missingPreferences = onboardingStateModel([
        'current_step' => OnboardingStep::PrivacyDiscovery->value,
        'introduction_completed_at' => '2026-08-30 10:01:00',
        'pet_relationship_choice' => OnboardingPetChoice::NotNow->value,
        'pet_relationship_completed_at' => '2026-08-30 10:03:00',
        'lock_version' => 4,
    ]);
    $unfinishedComplete = onboardingStateModel([
        'current_step' => OnboardingStep::Complete->value,
        'introduction_completed_at' => '2026-08-30 10:01:00',
        'preferences_completed_at' => '2026-08-30 10:02:00',
        'pet_relationship_choice' => OnboardingPetChoice::NotNow->value,
        'pet_relationship_completed_at' => '2026-08-30 10:03:00',
        'privacy_discovery_completed_at' => '2026-08-30 10:04:00',
        'completed_at' => null,
        'lock_version' => 5,
    ]);
    $invalidLock = onboardingStateModel([
        'current_step' => OnboardingStep::Complete->value,
        'introduction_completed_at' => '2026-08-30 10:01:00',
        'preferences_completed_at' => '2026-08-30 10:02:00',
        'pet_relationship_choice' => OnboardingPetChoice::NotNow->value,
        'pet_relationship_completed_at' => '2026-08-30 10:03:00',
        'privacy_discovery_completed_at' => '2026-08-30 10:04:00',
        'completed_at' => '2026-08-30 10:05:00',
        'lock_version' => 0,
    ]);
    $underVersionedComplete = onboardingStateModel([
        'current_step' => OnboardingStep::Complete->value,
        'introduction_completed_at' => '2026-08-30 10:01:00',
        'preferences_completed_at' => '2026-08-30 10:02:00',
        'pet_relationship_choice' => OnboardingPetChoice::NotNow->value,
        'pet_relationship_completed_at' => '2026-08-30 10:03:00',
        'privacy_discovery_completed_at' => '2026-08-30 10:04:00',
        'completed_at' => '2026-08-30 10:05:00',
        'lock_version' => 4,
    ]);
    $malformedTimestamp = onboardingStateModel([
        'current_step' => OnboardingStep::Complete->value,
        'introduction_completed_at' => '2026-08-30 10:01:00',
        'preferences_completed_at' => 'not-a-timestamp',
        'pet_relationship_choice' => OnboardingPetChoice::NotNow->value,
        'pet_relationship_completed_at' => '2026-08-30 10:03:00',
        'privacy_discovery_completed_at' => '2026-08-30 10:04:00',
        'completed_at' => '2026-08-30 10:05:00',
        'lock_version' => 5,
    ]);
    $malformedStartedAt = onboardingStateModel([
        'current_step' => OnboardingStep::Complete->value,
        'started_at' => 'not-a-timestamp',
        'introduction_completed_at' => '2026-08-30 10:01:00',
        'preferences_completed_at' => '2026-08-30 10:02:00',
        'pet_relationship_choice' => OnboardingPetChoice::NotNow->value,
        'pet_relationship_completed_at' => '2026-08-30 10:03:00',
        'privacy_discovery_completed_at' => '2026-08-30 10:04:00',
        'completed_at' => '2026-08-30 10:05:00',
        'lock_version' => 5,
    ]);

    expect($resolver->currentStep($unknown))->toBe(OnboardingStep::Introduction)
        ->and($resolver->isComplete($unknown))->toBeFalse()
        ->and($resolver->currentStep($missingPreferences))->toBe(OnboardingStep::Preferences)
        ->and($resolver->currentStep($unfinishedComplete))->toBe(OnboardingStep::PrivacyDiscovery)
        ->and($resolver->isComplete($unfinishedComplete))->toBeFalse()
        ->and($resolver->currentStep($invalidLock))->toBe(OnboardingStep::Introduction)
        ->and($resolver->isComplete($invalidLock))->toBeFalse()
        ->and($resolver->currentStep($underVersionedComplete))->toBe(OnboardingStep::Introduction)
        ->and($resolver->isComplete($underVersionedComplete))->toBeFalse()
        ->and($resolver->currentStep($malformedTimestamp))->toBe(OnboardingStep::Preferences)
        ->and($resolver->isComplete($malformedTimestamp))->toBeFalse()
        ->and($resolver->currentStep($malformedStartedAt))->toBe(OnboardingStep::Introduction)
        ->and($resolver->isComplete($malformedStartedAt))->toBeFalse();
});

test('it requires every persisted prerequisite before completion', function (): void {
    $resolver = new OnboardingState;
    $valid = onboardingStateModel([
        'current_step' => OnboardingStep::PrivacyDiscovery->value,
        'introduction_completed_at' => '2026-08-30 10:01:00',
        'preferences_completed_at' => '2026-08-30 10:02:00',
        'pet_relationship_choice' => OnboardingPetChoice::NotNow->value,
        'pet_relationship_completed_at' => '2026-08-30 10:03:00',
        'lock_version' => 4,
    ]);
    $invalidChoice = onboardingStateModel([
        'current_step' => OnboardingStep::PrivacyDiscovery->value,
        'introduction_completed_at' => '2026-08-30 10:01:00',
        'preferences_completed_at' => '2026-08-30 10:02:00',
        'pet_relationship_choice' => 'made-up-choice',
        'pet_relationship_completed_at' => '2026-08-30 10:03:00',
        'lock_version' => 4,
    ]);

    expect($resolver->currentPetChoice($valid))->toBe(OnboardingPetChoice::NotNow)
        ->and($resolver->hasCompletionPrerequisites($valid))->toBeTrue()
        ->and($resolver->currentPetChoice($invalidChoice))->toBeNull()
        ->and($resolver->currentStep($invalidChoice))->toBe(OnboardingStep::PetRelationship)
        ->and($resolver->hasCompletionPrerequisites($invalidChoice))->toBeFalse();
});
