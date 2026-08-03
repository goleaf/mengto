<?php

declare(strict_types=1);

namespace App\Livewire\Pets;

use App\Actions\CreatePetProfile as CreatePetProfileAction;
use App\Actions\StorePetPrimaryPhoto;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileVisibility;
use App\Livewire\Forms\PetProfileCreateForm;
use App\Livewire\Forms\PetProfileMediaForm;
use App\Models\User;
use App\Services\ProfilePresenter;
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

    #[Locked]
    public string $idempotencyKey = '';

    #[Locked]
    public string $mediaIdempotencyKey = '';

    private AuthFactory $auth;

    private CreatePetProfileAction $createAction;

    private StorePetPrimaryPhoto $storePhoto;

    private ProfilePresenter $profiles;

    public function boot(
        AuthFactory $auth,
        CreatePetProfileAction $createAction,
        StorePetPrimaryPhoto $storePhoto,
        ProfilePresenter $profiles,
    ): void {
        $this->auth = $auth;
        $this->createAction = $createAction;
        $this->storePhoto = $storePhoto;
        $this->profiles = $profiles;
    }

    public function mount(): void
    {
        $this->requireUser();
        $this->idempotencyKey = (string) Str::uuid();
        $this->mediaIdempotencyKey = (string) Str::uuid();
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

    public function create(): void
    {
        $this->requireUser();
        $media = $this->mediaForm->data();
        $profile = $this->createAction->handle(
            $this->form->creationData($this->idempotencyKey),
        );

        if ($media['upload'] instanceof TemporaryUploadedFile) {
            $this->storePhoto->handle(
                $profile,
                $media['upload'],
                $media['alt_text'],
                $this->mediaIdempotencyKey,
            );
        }

        session()->flash('pet-profile-feedback', __('pet_profiles.feedback.created'));
        $this->redirectRoute('pets.manage.show', ['petProfile' => $profile->profile_key]);
    }

    public function clearPhoto(): void
    {
        $this->mediaForm->reset();
        $this->resetValidation(['mediaForm.upload', 'mediaForm.altText']);
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
