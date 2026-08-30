<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\AdvanceUserOnboarding;
use App\Actions\CompleteOnboardingPreferences;
use App\Actions\CompleteOnboardingPrivacy;
use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Enums\PetProfileAccessRequestStatus;
use App\Livewire\Forms\OnboardingPrivacyForm;
use App\Livewire\Forms\ProfilePreferencesForm;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\EmailVerificationMode;
use App\Services\SafeIntendedUrl;
use App\Services\SocialActorResolver;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class Onboarding extends Component
{
    public ProfilePreferencesForm $preferencesForm;

    public OnboardingPrivacyForm $privacyForm;

    public bool $introductionAcknowledged = false;

    public bool $privacyAcknowledged = false;

    #[Locked]
    public string $expectedStep = '';

    #[Locked]
    public int $onboardingLockVersion = 0;

    #[Locked]
    public int $socialSettingsLockVersion = 0;

    private AuthFactory $auth;

    private EmailVerificationMode $emailVerification;

    private SocialActorResolver $actors;

    private AdvanceUserOnboarding $advanceOnboarding;

    private CompleteOnboardingPreferences $completePreferences;

    private CompleteOnboardingPrivacy $completePrivacy;

    private SafeIntendedUrl $intendedUrl;

    public function boot(
        AuthFactory $auth,
        EmailVerificationMode $emailVerification,
        SocialActorResolver $actors,
        AdvanceUserOnboarding $advanceOnboarding,
        CompleteOnboardingPreferences $completePreferences,
        CompleteOnboardingPrivacy $completePrivacy,
        SafeIntendedUrl $intendedUrl,
    ): void {
        $this->auth = $auth;
        $this->emailVerification = $emailVerification;
        $this->actors = $actors;
        $this->advanceOnboarding = $advanceOnboarding;
        $this->completePreferences = $completePreferences;
        $this->completePrivacy = $completePrivacy;
        $this->intendedUrl = $intendedUrl;
    }

    public function mount(): void
    {
        $user = $this->requireUser();
        $state = $user->onboarding()->first();

        if (! $state instanceof UserOnboarding || $state->isComplete()) {
            $this->redirect($this->intendedUrl->pull(route('home')));

            return;
        }

        $this->preferencesForm->fillFromUser($user);
        $actor = $this->actors->forUser($user);
        $this->privacyForm->fillFrom($actor, $actor->settings()->firstOrFail());
        $this->syncSnapshot($state);
    }

    /** @return array<string, string> */
    #[Computed]
    public function localeOptions(): array
    {
        return collect(config('platform.supported_locales', ['en']))
            ->mapWithKeys(static fn (string $locale): array => [
                $locale => __('auth.locales.'.$locale),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function timezoneOptions(): array
    {
        return collect(timezone_identifiers_list())
            ->mapWithKeys(static fn (string $timezone): array => [$timezone => $timezone])
            ->all();
    }

    #[Computed]
    public function hasManagedPet(): bool
    {
        $user = $this->requireUser();

        return PetProfile::query()
            ->managedBy($user)
            ->exists();
    }

    #[Computed]
    public function hasPendingAccessRequest(): bool
    {
        return PetProfileAccessRequest::query()
            ->whereBelongsTo($this->requireUser(), 'requester')
            ->where('status', PetProfileAccessRequestStatus::Pending)
            ->whereHas('profile')
            ->exists();
    }

    #[Computed]
    public function progressPosition(): int
    {
        $step = OnboardingStep::tryFrom($this->expectedStep);

        return min($step?->position() ?? 1, 4);
    }

    public function acknowledgeIntroduction(): void
    {
        $this->runMutation(function (): UserOnboarding {
            $this->validate([
                'introductionAcknowledged' => ['accepted'],
            ], [
                'introductionAcknowledged.accepted' => __('onboarding.validation.acknowledgement'),
            ]);

            return $this->advanceOnboarding->handle(
                $this->requireUser(),
                $this->snapshotStep(),
                $this->onboardingLockVersion,
            );
        });
    }

    public function savePreferences(): void
    {
        $state = $this->runMutation(function (): UserOnboarding {
            $data = $this->preferencesForm->validatedData();

            return $this->completePreferences->handle(
                $this->requireUser(),
                $data,
                $this->snapshotStep(),
                $this->onboardingLockVersion,
            );
        });

        if ($state instanceof UserOnboarding) {
            Session::put('locale', $this->requireUser()->fresh()->locale);
            $this->redirectRoute('onboarding.show');
        }
    }

    public function confirmPetRelationship(string $choice): void
    {
        $petChoice = OnboardingPetChoice::tryFrom($choice);

        if (! $petChoice instanceof OnboardingPetChoice) {
            $this->addError('petChoice', __('onboarding.validation.pet_choice'));
            $this->dispatch('onboarding-validation-failed');

            return;
        }

        $this->runMutation(function () use ($petChoice): UserOnboarding {
            return $this->advanceOnboarding->handle(
                $this->requireUser(),
                $this->snapshotStep(),
                $this->onboardingLockVersion,
                $petChoice,
            );
        });
    }

    public function savePrivacy(): void
    {
        $state = $this->runMutation(function (): UserOnboarding {
            $this->validate([
                'privacyAcknowledged' => ['accepted'],
            ], [
                'privacyAcknowledged.accepted' => __('onboarding.validation.privacy_acknowledgement'),
            ]);
            $data = $this->privacyForm->validatedData();

            return $this->completePrivacy->handle(
                user: $this->requireUser(),
                privacyAcknowledged: $this->privacyAcknowledged,
                isDiscoverable: $data['isDiscoverable'],
                isRecommendable: $data['isRecommendable'],
                allowMessageRequests: $data['allowMessageRequests'],
                expectedStep: $this->snapshotStep(),
                expectedOnboardingLockVersion: $this->onboardingLockVersion,
                expectedSocialSettingsLockVersion: $this->socialSettingsLockVersion,
            );
        });

        if ($state?->isComplete()) {
            Session::flash('feedback', __('onboarding.completion.feedback'));
            $this->redirect($this->intendedUrl->pull(route('home')));
        }
    }

    public function render(): View
    {
        return view('livewire.onboarding')
            ->layout('components.onboarding-layout', [
                'title' => __('onboarding.page.title'),
                'htmlLocale' => str_replace('_', '-', app()->getLocale()),
            ]);
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();
        abort_unless(
            $user instanceof User
                && $user->isActive()
                && $this->emailVerification->allows($user),
            403,
        );

        return $user;
    }

    private function snapshotStep(): OnboardingStep
    {
        $step = OnboardingStep::tryFrom($this->expectedStep);

        if (! $step instanceof OnboardingStep) {
            throw ValidationException::withMessages([
                'onboarding' => __('onboarding.errors.stale_state'),
            ]);
        }

        return $step;
    }

    /** @param callable(): UserOnboarding $mutation */
    private function runMutation(callable $mutation): ?UserOnboarding
    {
        try {
            $state = $mutation();
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->errors());
            $this->dispatch('onboarding-validation-failed');

            return null;
        }

        $this->resetErrorBag();
        $this->syncSnapshot($state);
        $this->dispatch('onboarding-step-changed');

        return $state;
    }

    private function syncSnapshot(UserOnboarding $state): void
    {
        $this->expectedStep = $state->current_step->value;
        $this->onboardingLockVersion = $state->lock_version;
        $actor = $this->actors->forUser($this->requireUser());
        $this->socialSettingsLockVersion = $actor->settings()->firstOrFail()->lock_version;
        unset($this->hasManagedPet, $this->hasPendingAccessRequest);
    }
}
