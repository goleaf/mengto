<?php

declare(strict_types=1);

namespace App\Livewire\Pets;

use App\Actions\CreatePetProfile as CreatePetProfileAction;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileVisibility;
use App\Livewire\Forms\PetProfileForm;
use App\Models\User;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class CreatePetProfile extends Component
{
    public PetProfileForm $form;

    #[Locked]
    public string $idempotencyKey = '';

    private AuthFactory $auth;

    private CreatePetProfileAction $createAction;

    private ProfilePresenter $profiles;

    public function boot(
        AuthFactory $auth,
        CreatePetProfileAction $createAction,
        ProfilePresenter $profiles,
    ): void {
        $this->auth = $auth;
        $this->createAction = $createAction;
        $this->profiles = $profiles;
    }

    public function mount(): void
    {
        $this->requireUser();
        $this->idempotencyKey = (string) Str::uuid();
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
    public function relationshipOptions(): array
    {
        return collect([
            PetManagerRole::PrimaryOwner,
            PetManagerRole::CoOwner,
            PetManagerRole::FamilyMember,
            PetManagerRole::Shelter,
            PetManagerRole::Volunteer,
            PetManagerRole::Finder,
            PetManagerRole::FosterCarer,
            PetManagerRole::Specialist,
            PetManagerRole::Other,
        ])->mapWithKeys(static fn (PetManagerRole $role): array => [
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

    public function create(): void
    {
        $this->requireUser();
        $profile = $this->createAction->handle(
            $this->form->creationData($this->idempotencyKey),
        );

        session()->flash('pet-profile-feedback', __('pet_profiles.feedback.created'));
        $this->redirectRoute('pets.manage.show', ['petProfile' => $profile->profile_key]);
    }

    public function render(): View
    {
        return view('livewire.pets.create-pet-profile')
            ->layout('components.livewire-app-layout', [
                'owner' => $this->profiles->owner(),
                'title' => __('pet_profiles.create.title'),
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
