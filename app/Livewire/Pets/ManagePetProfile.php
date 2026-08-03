<?php

declare(strict_types=1);

namespace App\Livewire\Pets;

use App\Actions\InvitePetProfileManager;
use App\Actions\RecordPetProfileFact;
use App\Actions\RemovePetPrimaryPhoto;
use App\Actions\RestorePetPrimaryPhoto;
use App\Actions\RevokePetProfileManager;
use App\Actions\StorePetPrimaryPhoto;
use App\Actions\TransitionPetProfileStatus;
use App\Actions\UpdatePetProfilePrivacy;
use App\Actions\UpdatePetProfileStep;
use App\Enums\PetEvidenceStatus;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileCompletionStep;
use App\Enums\PetProfileStatus;
use App\Enums\PetProfileVisibility;
use App\Livewire\Forms\PetManagerInvitationForm;
use App\Livewire\Forms\PetProfileDocumentsForm;
use App\Livewire\Forms\PetProfileForm;
use App\Livewire\Forms\PetProfileMediaForm;
use App\Livewire\Forms\PetProfilePrivacyForm;
use App\Models\PetProfile;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\PetProfileMedia;
use App\Models\User;
use App\Services\PetProfileCompletionPresenter;
use App\Services\PetProfileLifecycle;
use App\Services\ProfilePresenter;
use App\Services\QrCodeGenerator;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class ManagePetProfile extends Component
{
    use WithFileUploads;

    public PetProfileForm $form;

    public PetProfilePrivacyForm $privacyForm;

    public PetProfileDocumentsForm $documentsForm;

    public PetManagerInvitationForm $invitationForm;

    public PetProfileMediaForm $mediaForm;

    #[Locked]
    public int $profileId = 0;

    #[Url(except: 'basics', history: true)]
    public string $step = 'basics';

    public string $targetStatus = '';

    public string $statusReason = '';

    public string $feedback = '';

    #[Locked]
    public string $mediaIdempotencyKey = '';

    #[Locked]
    public string $stepIdempotencyKey = '';

    private AuthFactory $auth;

    private Gate $gate;

    private ProfilePresenter $profiles;

    private PetProfileLifecycle $lifecycle;

    private UpdatePetProfileStep $updateStepAction;

    private RecordPetProfileFact $recordFactAction;

    private UpdatePetProfilePrivacy $privacyAction;

    private InvitePetProfileManager $inviteAction;

    private RevokePetProfileManager $revokeAction;

    private StorePetPrimaryPhoto $storePhoto;

    private RemovePetPrimaryPhoto $removePhoto;

    private RestorePetPrimaryPhoto $restorePhoto;

    private TransitionPetProfileStatus $transitionAction;

    private QrCodeGenerator $qrCodes;

    private PetProfileCompletionPresenter $completionPresenter;

    private ?PetProfile $loadedProfile = null;

    private ?bool $mayManageDocuments = null;

    public function boot(
        AuthFactory $auth,
        Gate $gate,
        ProfilePresenter $profiles,
        PetProfileLifecycle $lifecycle,
        PetProfileCompletionPresenter $completionPresenter,
        UpdatePetProfileStep $updateStepAction,
        RecordPetProfileFact $recordFactAction,
        UpdatePetProfilePrivacy $privacyAction,
        InvitePetProfileManager $inviteAction,
        RevokePetProfileManager $revokeAction,
        StorePetPrimaryPhoto $storePhoto,
        RemovePetPrimaryPhoto $removePhoto,
        RestorePetPrimaryPhoto $restorePhoto,
        TransitionPetProfileStatus $transitionAction,
        QrCodeGenerator $qrCodes,
    ): void {
        $this->auth = $auth;
        $this->gate = $gate;
        $this->profiles = $profiles;
        $this->lifecycle = $lifecycle;
        $this->completionPresenter = $completionPresenter;
        $this->updateStepAction = $updateStepAction;
        $this->recordFactAction = $recordFactAction;
        $this->privacyAction = $privacyAction;
        $this->inviteAction = $inviteAction;
        $this->revokeAction = $revokeAction;
        $this->storePhoto = $storePhoto;
        $this->removePhoto = $removePhoto;
        $this->restorePhoto = $restorePhoto;
        $this->transitionAction = $transitionAction;
        $this->qrCodes = $qrCodes;
    }

    public function mount(PetProfile $petProfile): void
    {
        $this->step = PetProfileCompletionStep::fromRequest($this->step)->value;
        $profileForAuthorization = PetProfile::query()
            ->select(['id', 'user_id', 'status'])
            ->findOrFail($petProfile->id);
        $this->gate->authorize('update', $profileForAuthorization);
        $this->profileId = $profileForAuthorization->id;
        $profile = $this->profileModel();
        $this->form->fillFromProfile($profile);
        $this->fillActiveStepForms($profile);
        $this->targetStatus = $profile->status->value;
        $this->feedback = (string) session('pet-profile-feedback', '');
        $this->mediaIdempotencyKey = (string) Str::uuid();
        $this->stepIdempotencyKey = (string) Str::uuid();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function completionSteps(): array
    {
        $profile = $this->profileModel();

        return array_map(
            static fn (array $step): array => [
                ...$step,
                'href' => route('pets.manage.show', [
                    'petProfile' => $profile->profile_key,
                    'step' => $step['value'],
                ]),
            ],
            $this->completionPresenter->present($profile, $this->activeStep()),
        );
    }

    public function goToStep(string $step): void
    {
        $this->step = PetProfileCompletionStep::fromRequest($step)->value;
        $this->feedback = '';
        $this->resetValidation();
        $this->forgetComputed();
        $this->fillActiveStepForms($this->profileModel());
    }

    public function updatedStep(string $step): void
    {
        $this->goToStep($step);
    }

    /** @return array<string, string> */
    #[Computed]
    public function speciesOptions(): array
    {
        return collect(config('pet_profiles.species_options', []))
            ->mapWithKeys(static fn (string $species): array => [
                $species => __("pet_profiles.species.{$species}"),
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
    public function invitationRoleOptions(): array
    {
        return collect([
            PetManagerRole::CoOwner,
            PetManagerRole::FamilyMember,
            PetManagerRole::FosterCarer,
            PetManagerRole::Sitter,
            PetManagerRole::Caregiver,
            PetManagerRole::ProfileAdministrator,
            PetManagerRole::Specialist,
            PetManagerRole::Volunteer,
            PetManagerRole::Other,
        ])->mapWithKeys(static fn (PetManagerRole $role): array => [
            $role->value => $role->label(),
        ])->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function statusOptions(): array
    {
        $profile = $this->profileModel();

        return collect(PetProfileStatus::cases())
            ->filter(fn (PetProfileStatus $status): bool => $status === $profile->status
                || $this->lifecycle->canTransition($profile->status, $status))
            ->filter(fn (PetProfileStatus $status): bool => $this->gate
                ->forUser($this->requireUser())
                ->allows('transition', [$profile, $status]))
            ->mapWithKeys(static fn (PetProfileStatus $status): array => [
                $status->value => $status->label(),
            ])->all();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function managers(): array
    {
        return $this->profileModel()->managers
            ->map(static fn (PetProfileManager $manager): array => [
                'id' => $manager->id,
                'name' => $manager->user instanceof User
                    ? $manager->user->name
                    : __('pet_profiles.managers.unavailable_user'),
                'role' => $manager->role->label(),
                'status' => $manager->status->label(),
                'ends_at' => $manager->ends_at?->toDateString(),
                'revocable' => $manager->role !== PetManagerRole::PrimaryOwner,
            ])->all();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function history(): array
    {
        return $this->profileModel()->lifecycleEvents
            ->map(static fn (PetProfileLifecycleEvent $event): array => [
                'id' => $event->id,
                'event' => __("pet_profiles.events.{$event->event_type}"),
                'actor' => $event->actor_key_snapshot,
                'occurred_at' => $event->occurred_at->toDayDateTimeString(),
            ])->all();
    }

    public function saveBasics(): void
    {
        $this->saveProfileStep(
            PetProfileCompletionStep::Basics,
            $this->form->basicsData(),
            'basics_saved',
        );
    }

    public function saveAgeAndSex(): void
    {
        $this->saveProfileStep(
            PetProfileCompletionStep::AgeAndSex,
            $this->form->ageAndSexData(),
            'age_sex_saved',
        );
    }

    public function saveBreedAndOrigin(): void
    {
        $this->saveProfileStep(
            PetProfileCompletionStep::BreedAndOrigin,
            $this->form->breedAndOriginData(),
            'breed_origin_saved',
        );
    }

    public function saveAppearance(): void
    {
        $this->saveProfileStep(
            PetProfileCompletionStep::Appearance,
            $this->form->appearanceData(),
            'appearance_saved',
        );
    }

    public function saveCharacter(): void
    {
        $this->saveProfileStep(
            PetProfileCompletionStep::Character,
            $this->form->characterData(),
            'character_saved',
        );
    }

    public function saveSocialPreferences(): void
    {
        $this->saveProfileStep(
            PetProfileCompletionStep::SocialPreferences,
            $this->form->socialPreferencesData(),
            'social_saved',
        );
    }

    public function saveLocation(): void
    {
        $this->saveProfileStep(
            PetProfileCompletionStep::Location,
            $this->form->locationData(),
            'location_saved',
        );
    }

    public function autoSaveStep(string $step): void
    {
        $requestedStep = PetProfileCompletionStep::tryFrom($step);

        if ($requestedStep === null
            || ! $requestedStep->supportsAutosave()
            || $requestedStep !== $this->activeStep()) {
            throw ValidationException::withMessages([
                'step' => __('pet_profiles.validation.step'),
            ]);
        }

        [$data, $feedbackKey] = match ($requestedStep) {
            PetProfileCompletionStep::Basics => [$this->form->basicsData(), 'basics_saved'],
            PetProfileCompletionStep::AgeAndSex => [$this->form->ageAndSexData(), 'age_sex_saved'],
            PetProfileCompletionStep::BreedAndOrigin => [$this->form->breedAndOriginData(), 'breed_origin_saved'],
            PetProfileCompletionStep::Appearance => [$this->form->appearanceData(), 'appearance_saved'],
            PetProfileCompletionStep::Character => [$this->form->characterData(), 'character_saved'],
            PetProfileCompletionStep::SocialPreferences => [$this->form->socialPreferencesData(), 'social_saved'],
            PetProfileCompletionStep::Location => [$this->form->locationData(), 'location_saved'],
            default => throw ValidationException::withMessages([
                'step' => __('pet_profiles.validation.step'),
            ]),
        };

        $this->saveProfileStep($requestedStep, $data, $feedbackKey);
    }

    public function saveDocuments(): void
    {
        $profile = $this->profileModel();
        $existingIdentifier = $this->currentMicrochipIdentifier($profile);
        $value = $this->documentsForm->data(
            $this->documentsForm->microchipStatus === 'chipped'
                && $existingIdentifier === null,
        );

        if ($value['status'] === 'chipped'
            && $value['identifier'] === null
            && $existingIdentifier !== null) {
            $value['identifier'] = $existingIdentifier;
        }
        $this->recordFactAction->handle(
            profile: $profile,
            factKey: 'microchip-record',
            value: $value,
            precision: $value['identifier'] === null ? 'unknown' : 'exact',
            sourceType: 'owner',
            sourceReference: null,
            verificationStatus: PetEvidenceStatus::Unverified,
            visibility: PetProfileVisibility::Private,
            expectedLockVersion: $profile->lock_version,
            idempotencyKey: 'pet-documents:'.Str::uuid(),
            metadata: ['workspace_step' => PetProfileCompletionStep::Documents->value],
        );
        $this->feedback = __('pet_profiles.feedback.documents_saved');
        $this->forgetComputed();
        $this->fillActiveStepForms($this->profileModel());
    }

    public function replacePrimaryPhoto(): void
    {
        $profile = $this->profileModel();
        $this->gate->authorize('manageMedia', $profile);
        $media = $this->mediaForm->data(true);
        $upload = $media['upload'];

        if (! $upload instanceof TemporaryUploadedFile) {
            return;
        }

        $this->storePhoto->handle(
            $profile,
            $upload,
            $media['alt_text'],
            $this->mediaIdempotencyKey,
        );
        $this->mediaForm->reset();
        $this->mediaIdempotencyKey = (string) Str::uuid();
        $this->feedback = __('pet_profiles.feedback.photo_saved');
        $this->forgetComputed();
    }

    public function clearPhoto(): void
    {
        $this->mediaForm->reset();
        $this->resetValidation(['mediaForm.upload', 'mediaForm.altText']);
    }

    public function removePrimaryPhoto(): void
    {
        $profile = $this->profileModel();
        $this->removePhoto->handle($profile, 'pet-photo-remove:'.Str::uuid());
        $this->feedback = __('pet_profiles.feedback.photo_removed');
        $this->forgetComputed();
    }

    public function restorePrimaryPhoto(string $mediaKey): void
    {
        $profile = $this->profileModel();
        $this->gate->authorize('manageMedia', $profile);
        $media = $profile->media()
            ->where('media_key', $mediaKey)
            ->firstOrFail();
        $this->restorePhoto->handle(
            $profile,
            $media,
            'pet-photo-restore:'.Str::uuid(),
        );
        $this->feedback = __('pet_profiles.feedback.photo_restored');
        $this->forgetComputed();
    }

    public function savePrivacy(): void
    {
        $profile = $this->profileModel();
        $this->gate->authorize('managePrivacy', $profile);
        $updated = $this->privacyAction->handle(
            $profile->slug,
            $this->privacyForm->data($profile->lock_version, (string) Str::uuid()),
        );
        $this->privacyForm->fillFromProfile($updated->load('privacySetting'));
        $this->feedback = __('pet_profiles.feedback.privacy_saved');
        $this->forgetComputed();
    }

    public function inviteManager(): void
    {
        $profile = $this->profileModel();
        $this->gate->authorize('manageManagers', $profile);
        $data = $this->invitationForm->data();
        $invitee = User::query()
            ->select(['id', 'actor_key', 'email', 'name', 'status'])
            ->where('email', $data['email'])
            ->firstOrFail();
        abort_unless($invitee->isActive(), 422);
        $endsAt = $data['ends_at'] === null ? null : Carbon::parse($data['ends_at']);
        $this->inviteAction->handle(
            $profile,
            $invitee,
            $data['role'],
            $endsAt,
            [],
            'pet-manager-invite:'.Str::uuid(),
        );
        $this->invitationForm->reset();
        $this->feedback = __('pet_profiles.feedback.manager_invited');
        $this->forgetComputed();
    }

    public function revokeManager(int $managerId): void
    {
        $profile = $this->profileModel();
        $this->gate->authorize('manageManagers', $profile);
        $manager = $profile->managers()->findOrFail($managerId);
        $this->revokeAction->handle(
            $manager,
            'owner-revoked-access',
            'pet-manager-revoke:'.Str::uuid(),
        );
        $this->feedback = __('pet_profiles.feedback.manager_revoked');
        $this->forgetComputed();
    }

    public function transitionStatus(): void
    {
        $profile = $this->profileModel();
        $validated = $this->validate([
            'targetStatus' => ['required', Rule::enum(PetProfileStatus::class)],
            'statusReason' => ['nullable', 'string', 'max:500'],
        ]);
        $target = PetProfileStatus::from((string) $validated['targetStatus']);
        $reason = trim((string) ($validated['statusReason'] ?? ''));
        $updated = $this->transitionAction->handle(
            profile: $profile,
            target: $target,
            reasonCode: $reason !== '' ? 'owner-provided-reason' : 'owner-status-change',
            expectedLockVersion: $profile->lock_version,
            idempotencyKey: 'pet-status:'.Str::uuid(),
            privateMetadata: ['reason' => $reason],
        );
        $this->targetStatus = $updated->status->value;
        $this->statusReason = '';
        $this->feedback = __('pet_profiles.feedback.status_changed');
        $this->forgetComputed();
    }

    public function render(): View
    {
        $profile = $this->profileModel();
        $activeStep = $this->activeStep();
        $nextStep = $activeStep->next();
        $profileUrl = route('pets.profile', ['petProfile' => $profile->profile_key]);

        return view('livewire.pets.manage-pet-profile', [
            'profile' => $profile,
            'profileUrl' => $profileUrl,
            'currentStatusLabel' => $profile->status->label(),
            'today' => now()->toDateString(),
            'managerMinimumEnd' => now()->addMinute()->format('Y-m-d\TH:i'),
            'activeStep' => [
                'value' => $activeStep->value,
                'number' => $activeStep->number(),
                'label' => $activeStep->label(),
                'description' => $activeStep->description(),
                'why' => $activeStep->why(),
                'next_href' => $nextStep === null ? null : route('pets.manage.show', [
                    'petProfile' => $profile->profile_key,
                    'step' => $nextStep->value,
                ]),
            ],
            'primaryPhoto' => $activeStep === PetProfileCompletionStep::Photos
                ? $this->photoPresentation($profile->primaryMedia, $profile->profile_key)
                : null,
            'recoverablePhoto' => $activeStep === PetProfileCompletionStep::Photos
                ? $this->photoPresentation($profile->latestRecoverableMedia, $profile->profile_key)
                : null,
            'qrCode' => $activeStep === PetProfileCompletionStep::Preview
                ? $this->qrCodes->dataUri($profileUrl)
                : null,
            'hasMicrochipIdentifier' => $activeStep === PetProfileCompletionStep::Documents
                && $this->currentMicrochipIdentifier($profile) !== null,
            'canManageDocuments' => $this->canManageDocuments($profile),
        ])
            ->layout('components.livewire-app-layout', [
                'owner' => $this->profiles->owner(),
                'title' => __('pet_profiles.manage.title', ['name' => $profile->name]),
                'activeSection' => 'pets',
            ]);
    }

    private function profileModel(): PetProfile
    {
        if ($this->loadedProfile instanceof PetProfile) {
            return $this->loadedProfile;
        }

        $step = $this->activeStep();
        $user = $this->requireUser();
        $relations = match ($step) {
            PetProfileCompletionStep::Photos => [
                'primaryMedia.asset',
                'latestRecoverableMedia.asset',
            ],
            PetProfileCompletionStep::Owners => [
                'managers' => fn ($query) => $query
                    ->select([
                        'id',
                        'pet_profile_id',
                        'user_id',
                        'role',
                        'status',
                        'permission_overrides',
                        'starts_at',
                        'ends_at',
                        'revoked_at',
                    ])
                    ->with('user:id,name'),
            ],
            PetProfileCompletionStep::Privacy => ['privacySetting'],
            PetProfileCompletionStep::Preview => [
                'lifecycleEvents' => fn ($query) => $query
                    ->select([
                        'id',
                        'pet_profile_id',
                        'actor_key_snapshot',
                        'event_type',
                        'occurred_at',
                    ])
                    ->latest('occurred_at')
                    ->limit(20),
            ],
            default => [],
        };

        if ($step !== PetProfileCompletionStep::Owners) {
            $relations['managers'] = fn ($query) => $query
                ->select([
                    'id',
                    'pet_profile_id',
                    'user_id',
                    'role',
                    'status',
                    'permission_overrides',
                    'starts_at',
                    'ends_at',
                    'revoked_at',
                ])
                ->where('user_id', $user->id);
        }

        $profile = PetProfile::query()
            ->select([
                'id',
                'user_id',
                'profile_key',
                'slug',
                'name',
                'species',
                'taxon_id',
                'breed',
                'domestic_classification_id',
                'birth_date',
                'birth_date_precision',
                'sex',
                'reproductive_status',
                'visibility',
                'status',
                'creator_relationship',
                'is_discoverable',
                'allow_external_indexing',
                'lock_version',
                'profile_data',
            ])
            ->withExists([
                'primaryMedia',
                'managers',
                'privacySetting',
            ])
            ->with($relations)
            ->findOrFail($this->profileId);
        $this->gate->forUser($user)->authorize('update', $profile);

        if ($this->canManageDocuments($profile)) {
            $profile->loadExists('currentMicrochipRecord');

            if ($step === PetProfileCompletionStep::Documents) {
                $profile->load(['currentMicrochipRecord' => fn ($query) => $query->select([
                    'id',
                    'pet_profile_id',
                    'fact_key',
                    'value',
                    'precision',
                    'verification_status',
                    'visibility',
                    'is_current',
                    'current_key',
                    'recorded_at',
                ])]);
            }
        } else {
            $profile->setAttribute('current_microchip_record_exists', false);
            $profile->setRelation('currentMicrochipRecord', null);
        }

        return $this->loadedProfile = $profile;
    }

    /** @param array<string, mixed> $data */
    private function saveProfileStep(
        PetProfileCompletionStep $step,
        array $data,
        string $feedbackKey,
    ): void {
        $profile = $this->profileModel();
        $updated = $this->updateStepAction->handle(
            profile: $profile,
            step: $step,
            data: $data,
            expectedLockVersion: $profile->lock_version,
            idempotencyKey: 'pet-profile-step:'.$this->stepIdempotencyKey,
        );
        $this->form->fillFromProfile($updated);
        $this->feedback = __("pet_profiles.feedback.{$feedbackKey}");
        $this->stepIdempotencyKey = (string) Str::uuid();
        $this->forgetComputed();
    }

    private function fillActiveStepForms(PetProfile $profile): void
    {
        if ($this->activeStep() === PetProfileCompletionStep::Privacy
            && $profile->relationLoaded('privacySetting')) {
            $this->privacyForm->fillFromProfile($profile);
        }

        if ($this->activeStep() === PetProfileCompletionStep::Documents
            && $profile->relationLoaded('currentMicrochipRecord')) {
            $this->documentsForm->fillFromFact($profile->currentMicrochipRecord);
        }
    }

    private function activeStep(): PetProfileCompletionStep
    {
        return PetProfileCompletionStep::fromRequest($this->step);
    }

    private function currentMicrochipIdentifier(PetProfile $profile): ?string
    {
        if (! $profile->relationLoaded('currentMicrochipRecord')) {
            return null;
        }

        $fact = $profile->currentMicrochipRecord;

        if ($fact === null) {
            return null;
        }

        $identifier = $fact->value['identifier'] ?? null;

        return is_string($identifier) && trim($identifier) !== ''
            ? $identifier
            : null;
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();

        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }

    /** @return array{alt_text: string, media_key: string, url: string}|null */
    private function photoPresentation(
        ?PetProfileMedia $media,
        string $profileKey,
    ): ?array {
        if (! $media instanceof PetProfileMedia) {
            return null;
        }

        return [
            'alt_text' => $media->asset->alt_text
                ?? __('pet_profiles.public.avatar_alt', ['name' => $this->form->name]),
            'media_key' => $media->media_key,
            'url' => route('pets.media.show', [
                'petProfile' => $profileKey,
                'petProfileMedia' => $media->media_key,
            ]),
        ];
    }

    private function forgetComputed(): void
    {
        $this->loadedProfile = null;
        $this->mayManageDocuments = null;
        unset(
            $this->completionSteps,
            $this->managers,
            $this->history,
            $this->statusOptions,
        );
    }

    private function canManageDocuments(PetProfile $profile): bool
    {
        return $this->mayManageDocuments ??= $this->gate
            ->forUser($this->requireUser())
            ->allows('recordFact', [$profile, 'microchip-record']);
    }
}
