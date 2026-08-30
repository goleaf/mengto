<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\EmailVerificationMode;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CompleteOnboardingPreferences
{
    public function __construct(
        private ForumActor $actor,
        private EmailVerificationMode $emailVerification,
        private UpdateProfilePreferences $updatePreferences,
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

            if ($state->current_step->position() > OnboardingStep::Preferences->position()) {
                return $state;
            }

            if (
                $expectedStep !== OnboardingStep::Preferences
                || $state->current_step !== OnboardingStep::Preferences
                || $state->lock_version !== $expectedLockVersion
            ) {
                throw ValidationException::withMessages([
                    'onboarding' => __('onboarding.errors.stale_state'),
                ]);
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
}
