<?php

declare(strict_types=1);

namespace App\Livewire\Pets;

use App\Actions\InvitePetProfileManager;
use App\Actions\RevokePetProfileManager;
use App\Actions\TransitionPetProfileStatus;
use App\Actions\UpdatePetProfile;
use App\Actions\UpdatePetProfilePrivacy;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileStatus;
use App\Enums\PetProfileVisibility;
use App\Livewire\Forms\PetManagerInvitationForm;
use App\Livewire\Forms\PetProfileForm;
use App\Livewire\Forms\PetProfilePrivacyForm;
use App\Models\PetProfile;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Services\PetProfileLifecycle;
use App\Services\ProfilePresenter;
use App\Services\QrCodeGenerator;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ManagePetProfile extends Component
{
    public PetProfileForm $form;

    public PetProfilePrivacyForm $privacyForm;

    public PetManagerInvitationForm $invitationForm;

    #[Locked]
    public int $profileId = 0;

    public string $targetStatus = '';

    public string $statusReason = '';

    public string $feedback = '';

    private AuthFactory $auth;

    private Gate $gate;

    private ProfilePresenter $profiles;

    private PetProfileLifecycle $lifecycle;

    private UpdatePetProfile $updateAction;

    private UpdatePetProfilePrivacy $privacyAction;

    private InvitePetProfileManager $inviteAction;

    private RevokePetProfileManager $revokeAction;

    private TransitionPetProfileStatus $transitionAction;

    private QrCodeGenerator $qrCodes;

    public function boot(
        AuthFactory $auth,
        Gate $gate,
        ProfilePresenter $profiles,
        PetProfileLifecycle $lifecycle,
        UpdatePetProfile $updateAction,
        UpdatePetProfilePrivacy $privacyAction,
        InvitePetProfileManager $inviteAction,
        RevokePetProfileManager $revokeAction,
        TransitionPetProfileStatus $transitionAction,
        QrCodeGenerator $qrCodes,
    ): void {
        $this->auth = $auth;
        $this->gate = $gate;
        $this->profiles = $profiles;
        $this->lifecycle = $lifecycle;
        $this->updateAction = $updateAction;
        $this->privacyAction = $privacyAction;
        $this->inviteAction = $inviteAction;
        $this->revokeAction = $revokeAction;
        $this->transitionAction = $transitionAction;
        $this->qrCodes = $qrCodes;
    }

    public function mount(PetProfile $petProfile): void
    {
        $profileForAuthorization = PetProfile::query()
            ->select(['id', 'user_id', 'status'])
            ->findOrFail($petProfile->id);
        $this->gate->authorize('update', $profileForAuthorization);
        $this->profileId = $profileForAuthorization->id;
        $profile = $this->profileModel();
        $this->form->fillFromProfile($profile);
        $this->privacyForm->fillFromProfile($profile);
        $this->targetStatus = $profile->status->value;
        $this->feedback = (string) session('pet-profile-feedback', '');
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
        $profile = $this->profileModel();
        $this->gate->authorize('update', $profile);
        $updated = $this->updateAction->handle(
            $profile->slug,
            $this->form->updateData($profile->lock_version, (string) Str::uuid()),
        );
        $this->form->fillFromProfile($updated);
        $this->feedback = __('pet_profiles.feedback.basics_saved');
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

        return view('livewire.pets.manage-pet-profile', [
            'profile' => $profile,
            'qrCode' => $this->qrCodes->dataUri(route('pets.profile', [
                'petProfile' => $profile->profile_key,
            ])),
        ])
            ->layout('components.livewire-app-layout', [
                'owner' => $this->profiles->owner(),
                'title' => __('pet_profiles.manage.title', ['name' => $profile->name]),
                'activeSection' => 'pets',
            ]);
    }

    private function profileModel(): PetProfile
    {
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
            ->with([
                'privacySetting',
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
            ])
            ->findOrFail($this->profileId);
        $this->gate->authorize('update', $profile);

        return $profile;
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();

        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }

    private function forgetComputed(): void
    {
        unset($this->managers, $this->history, $this->statusOptions);
    }
}
