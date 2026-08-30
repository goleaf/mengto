<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Models\UserOnboarding;

final class OnboardingState
{
    public function currentStep(UserOnboarding $state): OnboardingStep
    {
        $stored = $state->persistedStep();
        $lockVersion = $state->persistedLockVersion();

        if (
            ! $stored instanceof OnboardingStep
            || $lockVersion === null
            || $lockVersion < $stored->position()
        ) {
            return OnboardingStep::Introduction;
        }

        if (! $state->hasPersistedTimestamp('started_at')) {
            return OnboardingStep::Introduction;
        }

        if (
            $stored->position() > OnboardingStep::Introduction->position()
            && ! $state->hasPersistedTimestamp('introduction_completed_at')
        ) {
            return OnboardingStep::Introduction;
        }

        if (
            $stored->position() > OnboardingStep::Preferences->position()
            && ! $state->hasPersistedTimestamp('preferences_completed_at')
        ) {
            return OnboardingStep::Preferences;
        }

        if (
            $stored->position() > OnboardingStep::PetRelationship->position()
            && (
                ! $state->hasPersistedTimestamp('pet_relationship_completed_at')
                || ! $state->persistedPetChoice() instanceof OnboardingPetChoice
            )
        ) {
            return OnboardingStep::PetRelationship;
        }

        if (
            $stored === OnboardingStep::Complete
            && (
                ! $state->hasPersistedTimestamp('privacy_discovery_completed_at')
                || ! $state->hasPersistedTimestamp('completed_at')
            )
        ) {
            return OnboardingStep::PrivacyDiscovery;
        }

        return $stored;
    }

    public function currentPetChoice(UserOnboarding $state): ?OnboardingPetChoice
    {
        return $state->persistedPetChoice();
    }

    public function nextStep(UserOnboarding $state): OnboardingStep
    {
        return $this->currentStep($state)->next();
    }

    public function previousStep(UserOnboarding $state): ?OnboardingStep
    {
        return match ($this->currentStep($state)) {
            OnboardingStep::Introduction => null,
            OnboardingStep::Preferences => OnboardingStep::Introduction,
            OnboardingStep::PetRelationship => OnboardingStep::Preferences,
            OnboardingStep::PrivacyDiscovery => OnboardingStep::PetRelationship,
            OnboardingStep::Complete => OnboardingStep::PrivacyDiscovery,
        };
    }

    public function canEnter(UserOnboarding $state, OnboardingStep $requested): bool
    {
        $current = $this->currentStep($state);

        return $current !== OnboardingStep::Complete && $requested === $current;
    }

    public function isComplete(UserOnboarding $state): bool
    {
        return $this->currentStep($state) === OnboardingStep::Complete
            && $state->isComplete();
    }

    public function hasCompletionPrerequisites(UserOnboarding $state): bool
    {
        return $this->currentStep($state) === OnboardingStep::PrivacyDiscovery
            && $state->hasPersistedTimestamp('started_at')
            && $state->hasPersistedTimestamp('introduction_completed_at')
            && $state->hasPersistedTimestamp('preferences_completed_at')
            && $state->hasPersistedTimestamp('pet_relationship_completed_at')
            && $state->persistedPetChoice() instanceof OnboardingPetChoice
            && ($state->persistedLockVersion() ?? 0) >= OnboardingStep::PrivacyDiscovery->position();
    }
}
