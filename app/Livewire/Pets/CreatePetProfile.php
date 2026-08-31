<?php

declare(strict_types=1);

namespace App\Livewire\Pets;

use App\Actions\CreatePetProfile as CreatePetProfileAction;
use App\Actions\StorePetPrimaryPhoto;
use App\Actions\SubmitPetProfileAccessRequest;
use App\Enums\OnboardingStep;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileAccessRequestType;
use App\Enums\PetProfileVisibility;
use App\Enums\PetSpeciesConfidence;
use App\Livewire\Forms\PetProfileAccessRequestForm;
use App\Livewire\Forms\PetProfileCreateForm;
use App\Livewire\Forms\PetProfileMediaForm;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\EmailVerificationMode;
use App\Services\OnboardingState;
use App\Services\PetProfileDuplicateReview;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class CreatePetProfile extends Component
{
    use WithFileUploads;

    public PetProfileCreateForm $form;

    public PetProfileMediaForm $mediaForm;

    public PetProfileAccessRequestForm $accessRequestForm;

    #[Locked]
    public string $idempotencyKey = '';

    #[Locked]
    public string $mediaIdempotencyKey = '';

    #[Locked]
    public string $accessRequestIdempotencyKey = '';

    #[Locked]
    public string $duplicateReviewToken = '';

    #[Locked]
    public string $selectedDuplicateProfileKey = '';

    #[Locked]
    public int $mountedUserId = 0;

    public string $accessRequestFeedback = '';

    private AuthFactory $auth;

    private CreatePetProfileAction $createAction;

    private StorePetPrimaryPhoto $storePhoto;

    private SubmitPetProfileAccessRequest $submitAccessRequest;

    private PetProfileDuplicateReview $duplicateReview;

    private EmailVerificationMode $emailVerification;

    private OnboardingState $onboardingState;

    private bool $mounting = false;

    public function boot(
        AuthFactory $auth,
        CreatePetProfileAction $createAction,
        StorePetPrimaryPhoto $storePhoto,
        SubmitPetProfileAccessRequest $submitAccessRequest,
        PetProfileDuplicateReview $duplicateReview,
        EmailVerificationMode $emailVerification,
        OnboardingState $onboardingState,
    ): void {
        $this->auth = $auth;
        $this->createAction = $createAction;
        $this->storePhoto = $storePhoto;
        $this->submitAccessRequest = $submitAccessRequest;
        $this->duplicateReview = $duplicateReview;
        $this->emailVerification = $emailVerification;
        $this->onboardingState = $onboardingState;
    }

    public function mount(): void
    {
        $this->mounting = true;

        try {
            $this->mountedUserId = (int) $this->requireUser()->getKey();
            $this->idempotencyKey = (string) Str::uuid();
            $this->mediaIdempotencyKey = (string) Str::uuid();
            $this->accessRequestIdempotencyKey = (string) Str::uuid();
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
    public function speciesOptions(): array
    {
        return collect(config('pet_profiles.species_options', []))
            ->mapWithKeys(static fn (string $species): array => [
                $species => __("pet_profiles.species.{$species}"),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function speciesConfidenceOptions(): array
    {
        return collect(PetSpeciesConfidence::optionsFor($this->form->species))
            ->mapWithKeys(static fn (PetSpeciesConfidence $confidence): array => [
                $confidence->value => $confidence->label(),
            ])->all();
    }

    public function updatedFormSpecies(string $species): void
    {
        $this->requireUser();
        $this->form->speciesConfidence = PetSpeciesConfidence::normalize(
            $species,
            $this->form->speciesConfidence,
        )->value;
        $this->duplicateReviewToken = '';
        $this->selectedDuplicateProfileKey = '';
        unset($this->speciesConfidenceOptions, $this->duplicateCandidates);
    }

    /** @return array<string, string> */
    #[Computed]
    public function relationshipOptions(): array
    {
        return collect(PetProfileCreateForm::relationshipRoles())
            ->mapWithKeys(static fn (PetManagerRole $role): array => [
                $role->value => $role->label(),
            ])->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function visibilityOptions(): array
    {
        return collect(PetProfileVisibility::cases())
            ->mapWithKeys(static fn (PetProfileVisibility $visibility): array => [
                $visibility->value => $visibility->label(),
            ])->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function accessRequestTypes(): array
    {
        return collect(PetProfileAccessRequestType::cases())
            ->mapWithKeys(static fn (PetProfileAccessRequestType $type): array => [
                $type->value => $type->label(),
            ])->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function correctionRoleOptions(): array
    {
        return collect(PetProfileAccessRequestForm::correctionRoles())
            ->mapWithKeys(static fn (PetManagerRole $role): array => [
                $role->value => $role->label(),
            ])->all();
    }

    /** @return array{url: string, label: string} */
    #[Computed]
    public function petSetupExit(): array
    {
        if ($this->isOnboardingPetRelationship($this->requireUser())) {
            return [
                'url' => route('onboarding.show'),
                'label' => __('pet_profiles.actions.back_to_onboarding'),
            ];
        }

        return [
            'url' => route('pets.index'),
            'label' => __('pet_profiles.actions.back_to_pets'),
        ];
    }

    /** @return list<array<string, string|null>> */
    #[Computed]
    public function duplicateCandidates(): array
    {
        if ($this->duplicateReviewToken === '') {
            return [];
        }

        return $this->duplicateReview->candidatesFromToken(
            $this->requireUser(),
            $this->form->name,
            $this->form->species,
            $this->duplicateReviewToken,
        );
    }

    public function create(): void
    {
        $user = $this->requireUser();
        $creationData = $this->form->creationData($this->idempotencyKey);
        $review = $this->duplicateReview->review(
            $user,
            $this->form->name,
            $this->form->species,
        );

        if ($review['candidates'] !== []) {
            $this->duplicateReviewToken = $review['token'];
            $this->selectedDuplicateProfileKey = '';
            $this->accessRequestFeedback = '';
            unset($this->duplicateCandidates);

            return;
        }

        $this->persistCreation($creationData);
    }

    public function confirmDifferentAnimal(): void
    {
        $user = $this->requireUser();
        $creationData = $this->form->creationData($this->idempotencyKey);
        $decisionToken = $this->duplicateReview->confirmDifferentAnimal(
            $user,
            $this->form->name,
            $this->form->species,
            $this->duplicateReviewToken,
        );

        if ($decisionToken === '') {
            $this->addError(
                'duplicate_review',
                __('pet_profiles.validation.duplicate_review_required'),
            );

            return;
        }

        $this->persistCreation($creationData, $decisionToken);
    }

    public function startAccessRequest(string $profileKey): void
    {
        $profile = $this->duplicateReview->candidateProfile(
            $this->requireUser(),
            $this->form->name,
            $this->form->species,
            $this->duplicateReviewToken,
            $profileKey,
        );
        abort_unless($profile !== null, 404);
        $this->selectedDuplicateProfileKey = $profile->profile_key;
        $this->accessRequestFeedback = '';
        $this->resetValidation([
            'accessRequestForm.requestType',
            'accessRequestForm.requestedRole',
            'accessRequestForm.evidenceSummary',
            'accessRequestForm.temporaryAccessEndsAt',
        ]);
    }

    public function cancelAccessRequest(): void
    {
        $this->requireUser();
        $this->selectedDuplicateProfileKey = '';
        $this->accessRequestForm->reset();
        $this->resetValidation();
    }

    public function submitSelectedAccessRequest(): void
    {
        $user = $this->requireUser();
        $profile = $this->duplicateReview->candidateProfile(
            $user,
            $this->form->name,
            $this->form->species,
            $this->duplicateReviewToken,
            $this->selectedDuplicateProfileKey,
        );
        abort_unless($profile !== null, 404);
        $data = $this->accessRequestForm->data();
        $this->submitAccessRequest->handle(
            $profile,
            $data['request_type'],
            $data['requested_role'],
            $data['evidence_summary'],
            $data['temporary_access_ends_at'],
            $this->accessRequestIdempotencyKey,
        );
        $this->selectedDuplicateProfileKey = '';
        $this->accessRequestForm->reset();

        if ($this->isOnboardingPetRelationship($user)) {
            session()->flash('feedback', __('pet_profiles.feedback.access_request_submitted'));
            $this->redirectRoute('onboarding.show');

            return;
        }

        $this->accessRequestFeedback = __('pet_profiles.feedback.access_request_submitted');
    }

    /** @param array<string, string> $creationData */
    private function persistCreation(array $creationData, string $decisionToken = ''): void
    {
        $user = $this->requireUser();
        $media = $this->mediaForm->data();
        $profile = $this->createAction->handle(
            $creationData + [
                'duplicate_review_token' => $this->duplicateReviewToken,
                'duplicate_review_decision_token' => $decisionToken,
            ],
        );

        if ($media['upload'] instanceof TemporaryUploadedFile) {
            $this->storePhoto->handle(
                $profile,
                $media['upload'],
                $media['alt_text'],
                $this->mediaIdempotencyKey,
            );
        }

        if ($this->isOnboardingPetRelationship($user)) {
            session()->flash('feedback', __('pet_profiles.feedback.created'));
            $this->redirectRoute('onboarding.show');

            return;
        }

        session()->flash('pet-profile-feedback', __('pet_profiles.feedback.created'));
        $this->redirectRoute('pets.manage.show', ['petProfile' => $profile->profile_key]);
    }

    public function clearPhoto(): void
    {
        $this->requireUser();
        $this->mediaForm->reset();
        $this->resetValidation(['mediaForm.upload', 'mediaForm.altText']);
    }

    public function render(): View
    {
        $view = view('livewire.pets.create-pet-profile');

        if ($this->isOnboardingPetRelationship($this->requireUser())) {
            return $view->layout('components.onboarding-layout', [
                'title' => __('pet_profiles.create.title'),
                'htmlLocale' => str_replace('_', '-', app()->getLocale()),
            ]);
        }

        return $view->layout('components.livewire-app-layout', [
            'title' => __('pet_profiles.create.title'),
            'activeSection' => 'pets',
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

        $state = $user->onboarding()->first();

        abort_unless(
            ! $state instanceof UserOnboarding
                || $this->onboardingState->isComplete($state)
                || $this->onboardingState->currentStep($state) === OnboardingStep::PetRelationship,
            403,
        );

        return $user;
    }

    private function isOnboardingPetRelationship(User $user): bool
    {
        $state = $user->onboarding()->first();

        return $state instanceof UserOnboarding
            && ! $this->onboardingState->isComplete($state)
            && $this->onboardingState->currentStep($state) === OnboardingStep::PetRelationship;
    }
}
