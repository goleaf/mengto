<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Models\SocialActor;
use App\Models\SocialActorSetting;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\ActiveAuthenticatedUser;
use App\Services\OnboardingPetEvidence;
use App\Services\OnboardingState;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CompleteOnboardingPrivacy
{
    public function __construct(
        private ActiveAuthenticatedUser $principal,
        private UpdateSocialActorSettings $updateSettings,
        private OnboardingState $onboardingState,
        private OnboardingPetEvidence $petEvidence,
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
        $this->principal->require($user);

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
            $user = $this->principal->require($user, true);
            $state = UserOnboarding::query()
                ->whereBelongsTo($user)
                ->lockForUpdate()
                ->firstOrFail();

            if ($expectedStep !== OnboardingStep::PrivacyDiscovery) {
                $this->throwConflict();
            }

            if ($this->onboardingState->isComplete($state)) {
                [$actor, $settings] = $this->canonicalSocialIdentity($user);

                if (
                    $state->lock_version === $expectedOnboardingLockVersion + 1
                    && $settings->lock_version === $expectedSocialSettingsLockVersion + 1
                    && $actor->is_discoverable === $isDiscoverable
                    && $settings->is_recommendable === $isRecommendable
                    && $settings->allow_message_requests === $allowMessageRequests
                ) {
                    return $state;
                }

                $this->throwConflict();
            }

            if (
                $this->onboardingState->currentStep($state) !== OnboardingStep::PrivacyDiscovery
                || $state->lock_version !== $expectedOnboardingLockVersion
            ) {
                $this->throwConflict();
            }

            if (! $this->onboardingState->hasCompletionPrerequisites($state)) {
                throw ValidationException::withMessages([
                    'onboarding' => __('onboarding.errors.transition_conflict'),
                ]);
            }

            $petChoice = $this->onboardingState->currentPetChoice($state);

            if (
                ! $petChoice instanceof OnboardingPetChoice
                || ! $this->petEvidence->supportsForCompletion($user, $petChoice)
            ) {
                throw ValidationException::withMessages([
                    'petChoice' => __('onboarding.validation.pet_evidence'),
                ]);
            }

            [$actor, $settings] = $this->canonicalSocialIdentity($user);
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

            $completedAt = now();
            $state->forceFill([
                'current_step' => OnboardingStep::Complete,
                'privacy_discovery_completed_at' => $completedAt,
                'completed_at' => $completedAt,
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

    /** @return array{SocialActor, SocialActorSetting} */
    private function canonicalSocialIdentity(User $user): array
    {
        $actor = SocialActor::query()
            ->whereBelongsTo($user)
            ->lockForUpdate()
            ->first();
        $settings = $actor instanceof SocialActor
            ? SocialActorSetting::query()
                ->whereBelongsTo($actor, 'actor')
                ->lockForUpdate()
                ->first()
            : null;

        if (! $actor instanceof SocialActor || ! $settings instanceof SocialActorSetting) {
            $this->throwConflict();
        }

        return [$actor, $settings];
    }
}
