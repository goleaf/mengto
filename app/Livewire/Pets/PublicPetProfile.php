<?php

declare(strict_types=1);

namespace App\Livewire\Pets;

use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\PetProfileMedia;
use App\Services\PetSpeciesLabel;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PublicPetProfile extends Component
{
    #[Locked]
    public int $profileId = 0;

    private Gate $gate;

    private ProfilePresenter $profiles;

    private PetSpeciesLabel $speciesLabels;

    public function boot(
        Gate $gate,
        ProfilePresenter $profiles,
        PetSpeciesLabel $speciesLabels,
    ): void {
        $this->gate = $gate;
        $this->profiles = $profiles;
        $this->speciesLabels = $speciesLabels;
    }

    public function mount(PetProfile $petProfile): void
    {
        $profile = PetProfile::query()
            ->select([
                'id',
                'user_id',
                'visibility',
                'status',
                'is_discoverable',
            ])
            ->findOrFail($petProfile->id);
        $this->gate->authorize('view', $profile);
        $this->profileId = $profile->id;
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function pet(): array
    {
        $profile = PetProfile::query()
            ->select([
                'id',
                'user_id',
                'profile_key',
                'name',
                'species',
                'taxon_id',
                'breed',
                'birth_date',
                'birth_date_precision',
                'sex',
                'visibility',
                'status',
                'is_discoverable',
                'profile_data',
                'created_at',
            ])
            ->with([
                'privacySetting:id,pet_profile_id,owner_display_mode,manager_display_mode,public_location_precision,section_rules',
                'taxon.activeVersion:id,taxon_id,rank,scientific_name,is_active_version',
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
                    ->activeAt(now())
                    ->with('user:id,name'),
                'primaryMedia.asset',
            ])
            ->findOrFail($this->profileId);
        $this->gate->authorize('view', $profile);
        $profileData = $profile->profile_data ?? [];
        $settings = $profile->privacySetting;
        $ownerLabel = null;

        if ($settings?->owner_display_mode !== 'hidden') {
            $owner = $profile->managers
                ->first(fn (PetProfileManager $manager): bool => in_array(
                    $manager->role->value,
                    ['primary-owner', 'organization-administrator', 'shelter'],
                    true,
                ));
            $ownerLabel = $settings?->owner_display_mode === 'contact-button'
                ? __('pet_profiles.public.managed_profile')
                : $owner?->user?->name;
        }

        $primaryMedia = $profile->primaryMedia;
        $primaryAvatar = $primaryMedia instanceof PetProfileMedia
            ? route('pets.media.show', [
                'petProfile' => $profile->profile_key,
                'petProfileMedia' => $primaryMedia->media_key,
            ])
            : null;

        return [
            'profile_key' => $profile->profile_key,
            'name' => $profile->name,
            'species' => $this->speciesLabels->for($profile->species),
            'scientific_name' => $profile->taxon?->activeVersion?->scientific_name,
            'breed' => $profile->breed,
            'age' => $this->ageLabel($profile),
            'status' => $profile->status->label(),
            'bio' => (string) ($profileData['story'] ?? ''),
            'owner' => $ownerLabel,
            'location' => $settings?->public_location_precision === 'hidden'
                ? null
                : data_get($profileData, 'location'),
            'avatar' => $primaryAvatar ?? data_get($profileData, 'avatar'),
            'avatar_alt' => $primaryMedia?->asset->alt_text
                ?? __('pet_profiles.public.avatar_alt', ['name' => $profile->name]),
        ];
    }

    public function render(): View
    {
        $pet = $this->pet();

        return view('livewire.pets.public-pet-profile', ['pet' => $pet])
            ->layout('components.livewire-app-layout', [
                'owner' => $this->profiles->owner(),
                'title' => $pet['name'],
                'activeSection' => 'pets',
            ]);
    }

    private function ageLabel(PetProfile $profile): ?string
    {
        if ($profile->birth_date === null) {
            return null;
        }

        $years = $profile->birth_date->diffInYears(now());
        $prefix = $profile->birth_date_precision === 'exact'
            ? ''
            : __('pet_profiles.public.approximately').' ';

        return $prefix.trans_choice('pet_profiles.public.age_years', $years, ['count' => $years]);
    }
}
