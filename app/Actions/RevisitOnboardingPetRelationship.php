<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\ActiveAuthenticatedUser;
use App\Services\OnboardingState;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RevisitOnboardingPetRelationship
{
    public function __construct(
        private ActiveAuthenticatedUser $principal,
        private OnboardingState $onboardingState,
    ) {}

    public function handle(User $user, int $expectedLockVersion): UserOnboarding
    {
        $this->principal->require($user);

        return DB::transaction(function () use ($expectedLockVersion, $user): UserOnboarding {
            $user = $this->principal->require($user, true);
            $state = UserOnboarding::query()
                ->whereBelongsTo($user)
                ->lockForUpdate()
                ->firstOrFail();
            $currentStep = $this->onboardingState->currentStep($state);

            if (
                $currentStep === OnboardingStep::PetRelationship
                && $state->lock_version === $expectedLockVersion + 1
            ) {
                return $state;
            }

            if (
                $currentStep !== OnboardingStep::PrivacyDiscovery
                || $state->lock_version !== $expectedLockVersion
            ) {
                throw ValidationException::withMessages([
                    'onboarding' => __('onboarding.errors.stale_state'),
                ]);
            }

            $state->forceFill([
                'current_step' => OnboardingStep::PetRelationship,
                'lock_version' => $state->lock_version + 1,
            ])->saveOrFail();

            return $state->refresh();
        }, 3);
    }
}
