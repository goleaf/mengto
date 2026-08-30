<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\AdvanceUserOnboarding;
use App\Actions\CompleteOnboardingPreferences;
use App\Actions\CompleteOnboardingPrivacy;
use App\Actions\DeferOnboardingPetRelationship;
use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Livewire\Forms\OnboardingPetChoiceForm;
use App\Livewire\Forms\OnboardingPrivacyForm;
use App\Livewire\Forms\ProfilePreferencesForm;
use App\Models\PetProfile;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\AccountEntryDestination;
use App\Services\EmailVerificationMode;
use App\Services\OnboardingPetEvidence;
use App\Services\OnboardingState;
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

    public OnboardingPetChoiceForm $petForm;

    public bool $privacyAcknowledged = false;

    public bool $focusCurrentStep = false;

    #[Locked]
    public string $expectedStep = '';

    #[Locked]
    public int $onboardingLockVersion = 0;

    #[Locked]
    public int $socialSettingsLockVersion = 0;

    #[Locked]
    public int $mountedUserId = 0;

    private AuthFactory $auth;

    private EmailVerificationMode $emailVerification;

    private SocialActorResolver $actors;

    private AdvanceUserOnboarding $advanceOnboarding;

    private CompleteOnboardingPreferences $completePreferences;

    private CompleteOnboardingPrivacy $completePrivacy;

    private DeferOnboardingPetRelationship $deferPetRelationship;

    private AccountEntryDestination $entryDestination;

    private OnboardingState $onboardingState;

    private OnboardingPetEvidence $petEvidence;

    private bool $mounting = false;

    public function boot(
        AuthFactory $auth,
        EmailVerificationMode $emailVerification,
        SocialActorResolver $actors,
        AdvanceUserOnboarding $advanceOnboarding,
        CompleteOnboardingPreferences $completePreferences,
        CompleteOnboardingPrivacy $completePrivacy,
        DeferOnboardingPetRelationship $deferPetRelationship,
        AccountEntryDestination $entryDestination,
        OnboardingState $onboardingState,
        OnboardingPetEvidence $petEvidence,
    ): void {
        $this->auth = $auth;
        $this->emailVerification = $emailVerification;
        $this->actors = $actors;
        $this->advanceOnboarding = $advanceOnboarding;
        $this->completePreferences = $completePreferences;
        $this->completePrivacy = $completePrivacy;
        $this->deferPetRelationship = $deferPetRelationship;
        $this->entryDestination = $entryDestination;
        $this->onboardingState = $onboardingState;
        $this->petEvidence = $petEvidence;
    }

    public function mount(): void
    {
        $this->mounting = true;

        try {
            $user = $this->requireUser();
            $state = $user->onboarding()->first();

            if (! $state instanceof UserOnboarding || $this->onboardingState->isComplete($state)) {
                $this->redirect($this->entryDestination->urlFor($user, route('home')));

                return;
            }

            $this->mountedUserId = (int) $user->getKey();
            $this->focusCurrentStep = Session::pull('onboarding-focus-step', false) === true;
            $this->actors->provisionPrivateForUser($user);
            $this->syncSnapshot($state);
            $this->prepareCurrentStep($user, $state);
        } finally {
            $this->mounting = false;
        }
    }

    public function hydrate(): void
    {
        $this->requireUser();
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
    public function hasAccessRequestEvidence(): bool
    {
        return $this->petEvidence->supports(
            $this->requireUser(),
            OnboardingPetChoice::AccessRequested,
        );
    }

    #[Computed]
    public function needsPetEvidenceRecovery(): bool
    {
        $user = $this->requireUser();
        $state = $user->onboarding()->first();

        if (
            ! $state instanceof UserOnboarding
            || $this->onboardingState->currentStep($state) !== OnboardingStep::PrivacyDiscovery
        ) {
            return false;
        }

        $choice = $this->onboardingState->currentPetChoice($state);

        return $choice instanceof OnboardingPetChoice
            && $choice !== OnboardingPetChoice::NotNow
            && ! $this->petEvidence->supports($user, $choice);
    }

    #[Computed]
    public function progressPosition(): int
    {
        $step = OnboardingStep::tryFrom($this->expectedStep);

        return min($step?->position() ?? 1, 4);
    }

    #[Computed]
    public function completedProgressSteps(): int
    {
        return max(0, $this->progressPosition() - 1);
    }

    /**
     * @return list<array{step: string, number: int, label: string, status: 'complete'|'current'|'upcoming'}>
     */
    #[Computed]
    public function progressSteps(): array
    {
        $current = OnboardingStep::tryFrom($this->expectedStep) ?? OnboardingStep::Introduction;

        return collect([
            OnboardingStep::Introduction,
            OnboardingStep::Preferences,
            OnboardingStep::PetRelationship,
            OnboardingStep::PrivacyDiscovery,
        ])->map(static function (OnboardingStep $step) use ($current): array {
            $status = match (true) {
                $step === $current => 'current',
                $step->position() < $current->position() => 'complete',
                default => 'upcoming',
            };

            return [
                'step' => $step->value,
                'number' => $step->position(),
                'label' => __('onboarding.steps.'.str_replace('-', '_', $step->value).'.label'),
                'status' => $status,
            ];
        })->all();
    }

    public function acknowledgeIntroduction(): void
    {
        $this->runMutation(function (): UserOnboarding {
            return $this->advanceOnboarding->handle(
                $this->requireUser(),
                $this->snapshotStep(),
                $this->onboardingLockVersion,
                introductionAcknowledged: true,
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
            Session::flash('onboarding-focus-step', true);
            $this->redirectRoute('onboarding.show');
        }
    }

    public function savePetRelationship(): void
    {
        $this->runMutation(function (): UserOnboarding {
            $petChoice = $this->petForm->selectedChoice();

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

        if ($state instanceof UserOnboarding && $this->onboardingState->isComplete($state)) {
            Session::flash('feedback', __('onboarding.completion.feedback'));
            $user = $this->requireUser();
            $user->refresh();
            $this->redirect($this->entryDestination->urlFor($user, route('home')));
        }
    }

    public function deferPetRelationship(): void
    {
        $this->runMutation(fn (): UserOnboarding => $this->deferPetRelationship->handle(
            $this->requireUser(),
            $this->onboardingLockVersion,
        ));
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
                && $this->emailVerification->allows($user)
                && (
                    $this->mountedUserId === (int) $user->getKey()
                    || ($this->mounting && $this->mountedUserId === 0)
                ),
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
            if ($this->canonicalStepChanged()) {
                Session::flash('feedback', __('onboarding.states.progress_updated'));
                $this->redirectRoute('onboarding.show');

                return null;
            }

            $this->setErrorBag($this->componentErrors($exception));
            $this->dispatch('onboarding-validation-failed');

            return null;
        }

        $this->resetErrorBag();
        $this->syncSnapshot($state);
        $this->prepareCurrentStep($this->requireUser(), $state);
        $this->dispatch('onboarding-step-changed');

        return $state;
    }

    private function syncSnapshot(UserOnboarding $state): void
    {
        $this->expectedStep = $this->onboardingState->currentStep($state)->value;
        $this->onboardingLockVersion = $state->lock_version;
        unset(
            $this->progressSteps,
            $this->progressPosition,
            $this->completedProgressSteps,
            $this->hasManagedPet,
            $this->hasAccessRequestEvidence,
            $this->needsPetEvidenceRecovery,
        );
    }

    private function prepareCurrentStep(User $user, UserOnboarding $state): void
    {
        $step = $this->onboardingState->currentStep($state);

        if ($step === OnboardingStep::Preferences) {
            $this->preferencesForm->fillFromUser($user);

            return;
        }

        if ($step === OnboardingStep::PetRelationship) {
            $this->petForm->choice = '';

            return;
        }

        if ($step !== OnboardingStep::PrivacyDiscovery) {
            return;
        }

        $actor = $this->actors->provisionPrivateForUser($user);
        $settings = $actor->settings()->firstOrFail();
        $this->privacyForm->fillFrom($actor, $settings);
        $this->socialSettingsLockVersion = $settings->lock_version;
    }

    private function canonicalStepChanged(): bool
    {
        $state = $this->requireUser()->onboarding()->first();

        return $state instanceof UserOnboarding
            && $this->onboardingState->currentStep($state)->value !== $this->expectedStep;
    }

    /** @return array<string, list<string>> */
    private function componentErrors(ValidationException $exception): array
    {
        $errors = $exception->errors();

        if (isset($errors['petChoice'])) {
            $errors['petForm.choice'] = $errors['petChoice'];
            unset($errors['petChoice']);
        }

        return $errors;
    }
}
