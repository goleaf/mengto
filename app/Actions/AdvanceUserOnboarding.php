<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Enums\PetProfileAccessRequestStatus;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\EmailVerificationMode;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class AdvanceUserOnboarding
{
    public function __construct(
        private ForumActor $actor,
        private EmailVerificationMode $emailVerification,
    ) {}

    public function handle(
        User $user,
        OnboardingStep $expectedStep,
        int $expectedLockVersion,
        ?OnboardingPetChoice $petChoice = null,
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

            if ($state->current_step->position() > $expectedStep->position()) {
                if (
                    $expectedStep === OnboardingStep::PetRelationship
                    && $petChoice !== null
                    && $state->pet_relationship_choice !== $petChoice
                ) {
                    $this->throwConflict();
                }

                return $state;
            }

            if (
                $state->current_step !== $expectedStep
                || $state->lock_version !== $expectedLockVersion
            ) {
                $this->throwConflict();
            }

            $attributes = match ($expectedStep) {
                OnboardingStep::Introduction => [
                    'introduction_completed_at' => now(),
                ],
                OnboardingStep::PetRelationship => $this->petAttributes($user, $petChoice),
                default => throw ValidationException::withMessages([
                    'onboarding' => __('onboarding.errors.transition_conflict'),
                ]),
            };

            $state->forceFill($attributes + [
                'current_step' => $expectedStep->next(),
                'lock_version' => $state->lock_version + 1,
            ])->saveOrFail();

            return $state->refresh();
        }, 3);
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

        if ($choice === OnboardingPetChoice::ManagedPet) {
            $hasManagedPet = PetProfile::query()
                ->managedBy($user)
                ->exists();

            if (! $hasManagedPet) {
                $this->throwPetEvidence();
            }
        }

        if (
            $choice === OnboardingPetChoice::AccessRequested
            && ! PetProfileAccessRequest::query()
                ->whereBelongsTo($user, 'requester')
                ->where('status', PetProfileAccessRequestStatus::Pending)
                ->whereHas('profile')
                ->exists()
        ) {
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
