<?php

declare(strict_types=1);

namespace App\Livewire\Pets;

use App\Actions\AcceptPetProfileManagerInvitation;
use App\Enums\PetManagerStatus;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Services\PetSpeciesLabel;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class PetProfileInvitations extends Component
{
    public string $feedback = '';

    private AuthFactory $auth;

    private AcceptPetProfileManagerInvitation $acceptAction;

    private ProfilePresenter $profiles;

    private PetSpeciesLabel $speciesLabels;

    public function boot(
        AuthFactory $auth,
        AcceptPetProfileManagerInvitation $acceptAction,
        ProfilePresenter $profiles,
        PetSpeciesLabel $speciesLabels,
    ): void {
        $this->auth = $auth;
        $this->acceptAction = $acceptAction;
        $this->profiles = $profiles;
        $this->speciesLabels = $speciesLabels;
    }

    public function mount(): void
    {
        $this->requireUser();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function invitations(): array
    {
        return PetProfileManager::query()
            ->select([
                'id',
                'pet_profile_id',
                'user_id',
                'role',
                'status',
                'ends_at',
                'invited_by_user_id',
            ])
            ->where('user_id', $this->requireUser()->id)
            ->where('status', PetManagerStatus::Invited)
            ->with([
                'profile:id,profile_key,name,species,species_confidence,status',
                'inviter:id,name',
            ])
            ->orderByDesc('id')
            ->limit(25)
            ->get()
            ->map(fn (PetProfileManager $invitation): array => [
                'id' => $invitation->id,
                'pet_name' => $invitation->profile->name,
                'species' => $this->speciesLabels->for(
                    $invitation->profile->species,
                    $invitation->profile->species_confidence,
                ),
                'role' => $invitation->role->label(),
                'inviter' => $invitation->inviter instanceof User
                    ? $invitation->inviter->name
                    : __('pet_profiles.managers.unavailable_user'),
                'expires_at' => $invitation->ends_at?->toDayDateTimeString(),
            ])->all();
    }

    public function accept(int $invitationId): void
    {
        $invitation = PetProfileManager::query()
            ->where('user_id', $this->requireUser()->id)
            ->where('status', PetManagerStatus::Invited)
            ->findOrFail($invitationId);
        $this->acceptAction->handle(
            $invitation,
            'pet-manager-accept:'.Str::uuid(),
        );
        $this->feedback = __('pet_profiles.feedback.invitation_accepted');
        unset($this->invitations);
    }

    public function render(): View
    {
        return view('livewire.pets.pet-profile-invitations')
            ->layout('components.livewire-app-layout', [
                'owner' => $this->profiles->owner(),
                'title' => __('pet_profiles.invitations.title'),
                'activeSection' => 'pets',
            ]);
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();

        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }
}
