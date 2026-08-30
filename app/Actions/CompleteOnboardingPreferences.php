<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\EmailVerificationMode;
use App\Services\ForumActor;
use App\Services\OnboardingState;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CompleteOnboardingPreferences
{
    public function __construct(
        private ForumActor $actor,
        private EmailVerificationMode $emailVerification,
        private UpdateProfilePreferences $updatePreferences,
        private OnboardingState $onboardingState,
    ) {}

    /** @param array{locale: string, timezone: string} $data */
    public function handle(
        User $user,
        array $data,
        OnboardingStep $expectedStep,
        int $expectedLockVersion,
    ): UserOnboarding {
        $authenticated = $this->actor->requireUser();
        abort_unless(
            $authenticated->is($user)
                && $authenticated->isActive()
                && User::query()
                    ->whereKey($user->getKey())
                    ->where('status', UserStatus::Active)
                    ->exists()
                && $this->emailVerification->allows($authenticated),
            403,
        );

        return DB::transaction(function () use (
            $data,
            $expectedLockVersion,
            $expectedStep,
            $user,
        ): UserOnboarding {
            $state = UserOnboarding::query()
                ->whereBelongsTo($user)
                ->lockForUpdate()
                ->firstOrFail();

            if ($expectedStep !== OnboardingStep::Preferences) {
                $this->throwConflict();
            }

            $currentStep = $this->onboardingState->currentStep($state);

            if ($currentStep->position() > OnboardingStep::Preferences->position()) {
                $persistedUser = User::query()
                    ->select(['id', 'locale', 'timezone'])
                    ->findOrFail($user->id);

                if (
                    $currentStep === OnboardingStep::PetRelationship
                    && $state->lock_version === $expectedLockVersion + 1
                    && $state->getRawOriginal('preferences_completed_at') !== null
                    && $persistedUser->locale === ($data['locale'] ?? null)
                    && $persistedUser->timezone === ($data['timezone'] ?? null)
                ) {
                    return $state;
                }

                $this->throwConflict();
            }

            if (
                $currentStep !== OnboardingStep::Preferences
                || $state->lock_version !== $expectedLockVersion
            ) {
                $this->throwConflict();
            }

            $this->updatePreferences->handle($user, $data);
            $state->forceFill([
                'current_step' => OnboardingStep::PetRelationship,
                'preferences_completed_at' => now(),
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
