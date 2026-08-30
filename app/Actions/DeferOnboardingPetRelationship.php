<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\EmailVerificationMode;
use App\Services\ForumActor;
use App\Services\OnboardingPetEvidence;
use App\Services\OnboardingState;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class DeferOnboardingPetRelationship
{
    public function __construct(
        private ForumActor $account,
        private EmailVerificationMode $emailVerification,
        private OnboardingState $onboardingState,
        private OnboardingPetEvidence $petEvidence,
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

            if ($this->onboardingState->currentStep($state) !== OnboardingStep::PrivacyDiscovery) {
                $this->throwConflict();
            }

            if (
                $state->lock_version === $expectedLockVersion + 1
                && $this->onboardingState->currentPetChoice($state) === OnboardingPetChoice::AddLater
            ) {
                return $state;
            }

            if ($state->lock_version !== $expectedLockVersion) {
                $this->throwConflict();
            }

            $choice = $this->onboardingState->currentPetChoice($state);

            if ($choice instanceof OnboardingPetChoice && ! $choice->requiresPetEvidence()) {
                return $state;
            }

            if (! $choice instanceof OnboardingPetChoice) {
                $this->throwConflict();
            }

            if ($this->petEvidence->supportsForCompletion($user, $choice)) {
                throw ValidationException::withMessages([
                    'onboarding' => __('onboarding.errors.pet_evidence_current'),
                ]);
            }

            $state->forceFill([
                'pet_relationship_choice' => OnboardingPetChoice::AddLater,
                'lock_version' => $state->lock_version + 1,
            ])->saveOrFail();

            return $state->refresh();
        }, 3);
    }

    private function throwConflict(): never
    {
        throw ValidationException::withMessages([
            'onboarding' => __('onboarding.errors.stale_state'),
        ]);
    }
}
