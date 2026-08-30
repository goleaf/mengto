<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\EmailVerificationMode;
use App\Services\ForumActor;
use App\Services\OnboardingState;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RevisitOnboardingPetRelationship
{
    public function __construct(
        private ForumActor $account,
        private EmailVerificationMode $emailVerification,
        private OnboardingState $onboardingState,
    ) {}

    public function handle(User $user, int $expectedLockVersion): UserOnboarding
    {
        $authenticated = $this->account->requireUser();

        abort_unless(
            $authenticated->is($user)
                && $authenticated->isActive()
                && $this->emailVerification->allows($authenticated),
            403,
        );

        return DB::transaction(function () use ($expectedLockVersion, $user): UserOnboarding {
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
