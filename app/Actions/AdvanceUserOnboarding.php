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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class AdvanceUserOnboarding
{
    public function __construct(
        private ForumActor $actor,
        private EmailVerificationMode $emailVerification,
        private OnboardingState $onboardingState,
        private OnboardingPetEvidence $petEvidence,
    ) {}

    public function handle(
        User $user,
        OnboardingStep $expectedStep,
        int $expectedLockVersion,
        ?OnboardingPetChoice $petChoice = null,
        bool $introductionAcknowledged = false,
    ): UserOnboarding {
        $authenticated = $this->actor->requireUser();

        abort_unless(
            $authenticated->is($user)
                && $authenticated->isActive()
                && $this->emailVerification->allows($authenticated),
            403,
        );

        return DB::transaction(function () use (
            $expectedLockVersion,
            $expectedStep,
            $introductionAcknowledged,
            $petChoice,
            $user,
        ): UserOnboarding {
            $state = UserOnboarding::query()
                ->whereBelongsTo($user)
                ->lockForUpdate()
                ->first();

            if (! $state instanceof UserOnboarding) {
                throw ValidationException::withMessages([
                    'onboarding' => __('onboarding.errors.state_unavailable'),
                ]);
            }

            $currentStep = $this->onboardingState->currentStep($state);

            if ($currentStep->position() > $expectedStep->position()) {
                if (! $this->isEquivalentReplay(
                    $state,
                    $currentStep,
                    $expectedStep,
                    $expectedLockVersion,
                    $petChoice,
                    $introductionAcknowledged,
                )) {
                    $this->throwConflict();
                }

                return $state;
            }

            if (
                $currentStep !== $expectedStep
                || $state->lock_version !== $expectedLockVersion
            ) {
                $this->throwConflict();
            }

            $attributes = match ($expectedStep) {
                OnboardingStep::Introduction => $this->introductionAttributes(
                    $state,
                    $introductionAcknowledged,
                ),
                OnboardingStep::PetRelationship => $this->petAttributes($user, $petChoice),
                default => throw ValidationException::withMessages([
                    'onboarding' => __('onboarding.errors.transition_conflict'),
                ]),
            };

            if (
                $expectedStep === OnboardingStep::Introduction
                && ! $state->hasPersistedTimestamp('started_at')
            ) {
                return $this->repairMalformedStart($state, $attributes);
            }

            $state->forceFill($attributes + [
                'current_step' => $expectedStep->next(),
                'lock_version' => $state->lock_version + 1,
            ])->saveOrFail();

            return $state->refresh();
        }, 3);
    }

    /** @param array<string, mixed> $attributes */
    private function repairMalformedStart(
        UserOnboarding $state,
        array $attributes,
    ): UserOnboarding {
        $updated = UserOnboarding::query()
            ->whereKey($state->id)
            ->where('lock_version', $state->lock_version)
            ->update($attributes + [
                'current_step' => OnboardingStep::Preferences->value,
                'preferences_completed_at' => null,
                'pet_relationship_choice' => null,
                'pet_relationship_completed_at' => null,
                'privacy_discovery_completed_at' => null,
                'completed_at' => null,
                'lock_version' => $state->lock_version + 1,
            ]);

        if ($updated !== 1) {
            $this->throwConflict();
        }

        return UserOnboarding::query()->findOrFail($state->id);
    }

    private function isEquivalentReplay(
        UserOnboarding $state,
        OnboardingStep $currentStep,
        OnboardingStep $expectedStep,
        int $expectedLockVersion,
        ?OnboardingPetChoice $petChoice,
        bool $introductionAcknowledged,
    ): bool {
        if (
            $currentStep !== $expectedStep->next()
            || $state->lock_version !== $expectedLockVersion + 1
        ) {
            return false;
        }

        return match ($expectedStep) {
            OnboardingStep::Introduction => $introductionAcknowledged
                && $state->getRawOriginal('introduction_completed_at') !== null,
            OnboardingStep::PetRelationship => $petChoice instanceof OnboardingPetChoice
                && $this->onboardingState->currentPetChoice($state) === $petChoice,
            default => false,
        };
    }

    /** @return array{introduction_completed_at: Carbon, started_at?: Carbon} */
    private function introductionAttributes(
        UserOnboarding $state,
        bool $acknowledged,
    ): array {
        if (! $acknowledged) {
            throw ValidationException::withMessages([
                'introductionAcknowledged' => __('onboarding.validation.acknowledgement'),
            ]);
        }

        $completedAt = now();
        $attributes = ['introduction_completed_at' => $completedAt];

        if (! $state->hasPersistedTimestamp('started_at')) {
            $attributes['started_at'] = $completedAt;
        }

        return $attributes;
    }

    /** @return array<string, mixed> */
    private function petAttributes(
        User $user,
        ?OnboardingPetChoice $choice,
    ): array {
        if (! $choice instanceof OnboardingPetChoice) {
            throw ValidationException::withMessages([
                'petChoice' => __('onboarding.validation.pet_choice'),
            ]);
        }

        if (! $this->petEvidence->supports($user, $choice)) {
            $this->throwPetEvidence();
        }

        return [
            'pet_relationship_choice' => $choice,
            'pet_relationship_completed_at' => now(),
        ];
    }

    private function throwConflict(): never
    {
        throw ValidationException::withMessages([
            'onboarding' => __('onboarding.errors.stale_state'),
        ]);
    }

    private function throwPetEvidence(): never
    {
        throw ValidationException::withMessages([
            'petChoice' => __('onboarding.validation.pet_evidence'),
        ]);
    }
}
