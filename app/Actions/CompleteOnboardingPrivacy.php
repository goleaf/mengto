<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingStep;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\EmailVerificationMode;
use App\Services\ForumActor;
use App\Services\SocialActorResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CompleteOnboardingPrivacy
{
    public function __construct(
        private ForumActor $account,
        private EmailVerificationMode $emailVerification,
        private SocialActorResolver $actors,
        private UpdateSocialActorSettings $updateSettings,
    ) {}

    public function handle(
        User $user,
        bool $privacyAcknowledged,
        bool $isDiscoverable,
        bool $isRecommendable,
        bool $allowMessageRequests,
        OnboardingStep $expectedStep,
        int $expectedOnboardingLockVersion,
        int $expectedSocialSettingsLockVersion,
    ): UserOnboarding {
        $authenticated = $this->account->requireUser();
        abort_unless(
            $authenticated->is($user)
                && $authenticated->isActive()
                && $this->emailVerification->allows($authenticated),
            403,
        );

        if (! $privacyAcknowledged) {
            throw ValidationException::withMessages([
                'privacyAcknowledged' => __('onboarding.validation.privacy_acknowledgement'),
            ]);
        }

        return DB::transaction(function () use (
            $allowMessageRequests,
            $expectedOnboardingLockVersion,
            $expectedSocialSettingsLockVersion,
            $expectedStep,
            $isDiscoverable,
            $isRecommendable,
            $user,
        ): UserOnboarding {
            $state = UserOnboarding::query()
                ->whereBelongsTo($user)
                ->lockForUpdate()
                ->firstOrFail();

            if ($state->isComplete()) {
                return $state;
            }

            if (
                $expectedStep !== OnboardingStep::PrivacyDiscovery
                || $state->current_step !== OnboardingStep::PrivacyDiscovery
                || $state->lock_version !== $expectedOnboardingLockVersion
            ) {
                throw ValidationException::withMessages([
                    'onboarding' => __('onboarding.errors.stale_state'),
                ]);
            }

            $actor = $this->actors->forUser($user);
            $settings = $actor->settings()->firstOrFail();
            $this->updateSettings->handle(
                actor: $actor,
                friendRequestPolicy: $settings->friend_request_policy,
                followPolicy: $settings->follow_policy,
                friendListVisibility: $settings->friend_list_visibility,
                followerListVisibility: $settings->follower_list_visibility,
                isDiscoverable: $isDiscoverable,
                isRecommendable: $isRecommendable,
                allowMessageRequests: $allowMessageRequests,
                expectedLockVersion: $expectedSocialSettingsLockVersion,
            );

            $state->forceFill([
                'current_step' => OnboardingStep::Complete,
                'privacy_discovery_completed_at' => now(),
                'completed_at' => now(),
                'lock_version' => $state->lock_version + 1,
            ])->saveOrFail();

            return $state->refresh();
        }, 3);
    }
}
