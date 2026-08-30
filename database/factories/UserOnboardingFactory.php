<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserOnboarding;

/** @extends ApplicationFactory<UserOnboarding> */
final class UserOnboardingFactory extends ApplicationFactory
{
    protected $model = UserOnboarding::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'current_step' => OnboardingStep::Introduction,
            'pet_relationship_choice' => null,
            'started_at' => now(),
            'introduction_completed_at' => null,
            'preferences_completed_at' => null,
            'pet_relationship_completed_at' => null,
            'privacy_discovery_completed_at' => null,
            'completed_at' => null,
            'lock_version' => 1,
        ];
    }

    public function preferences(): self
    {
        return $this->state(fn (): array => [
            'current_step' => OnboardingStep::Preferences,
            'introduction_completed_at' => now(),
            'lock_version' => 2,
        ]);
    }

    public function petRelationship(): self
    {
        return $this->state(fn (): array => [
            'current_step' => OnboardingStep::PetRelationship,
            'introduction_completed_at' => now(),
            'preferences_completed_at' => now(),
            'lock_version' => 3,
        ]);
    }

    public function privacyDiscovery(
        OnboardingPetChoice $choice = OnboardingPetChoice::NotNow,
    ): self {
        return $this->state(fn (): array => [
            'current_step' => OnboardingStep::PrivacyDiscovery,
            'pet_relationship_choice' => $choice,
            'introduction_completed_at' => now(),
            'preferences_completed_at' => now(),
            'pet_relationship_completed_at' => now(),
            'lock_version' => 4,
        ]);
    }

    public function completed(
        OnboardingPetChoice $choice = OnboardingPetChoice::NotNow,
    ): self {
        return $this->state(fn (): array => [
            'current_step' => OnboardingStep::Complete,
            'pet_relationship_choice' => $choice,
            'introduction_completed_at' => now(),
            'preferences_completed_at' => now(),
            'pet_relationship_completed_at' => now(),
            'privacy_discovery_completed_at' => now(),
            'completed_at' => now(),
            'lock_version' => 5,
        ]);
    }
}
