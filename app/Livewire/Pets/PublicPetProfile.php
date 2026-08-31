<?php

declare(strict_types=1);

namespace App\Livewire\Pets;

use App\Enums\PetProfileNameVisibility;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\PetProfileMedia;
use App\Services\PetAppearancePresenter;
use App\Services\PetBodyCoveringPresenter;
use App\Services\PetBreedOriginPresenter;
use App\Services\PetIdentifyingMarkPresenter;
use App\Services\PetLifeStagePresenter;
use App\Services\PetProfileAgeLabel;
use App\Services\PetSizeCategoryPresenter;
use App\Services\PetSpeciesLabel;
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

    private PetSpeciesLabel $speciesLabels;

    private PetProfileAgeLabel $ageLabels;

    private PetBreedOriginPresenter $breedOrigins;

    private PetAppearancePresenter $appearance;

    private PetBodyCoveringPresenter $bodyCovering;

    private PetIdentifyingMarkPresenter $identifyingMarks;

    private PetLifeStagePresenter $lifeStages;

    private PetSizeCategoryPresenter $sizeCategories;

    public function boot(
        Gate $gate,
        PetProfileAgeLabel $ageLabels,
        PetBreedOriginPresenter $breedOrigins,
        PetAppearancePresenter $appearance,
        PetBodyCoveringPresenter $bodyCovering,
        PetIdentifyingMarkPresenter $identifyingMarks,
        PetLifeStagePresenter $lifeStages,
        PetSizeCategoryPresenter $sizeCategories,
        PetSpeciesLabel $speciesLabels,
    ): void {
        $this->gate = $gate;
        $this->ageLabels = $ageLabels;
        $this->breedOrigins = $breedOrigins;
        $this->appearance = $appearance;
        $this->bodyCovering = $bodyCovering;
        $this->identifyingMarks = $identifyingMarks;
        $this->lifeStages = $lifeStages;
        $this->sizeCategories = $sizeCategories;
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
        abort_unless($this->gate->allows('view', $profile), 404);
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
                'species_confidence',
                'taxon_id',
                'breed',
                'breed_origin_type',
                'size_category',
                'birth_date',
                'birth_date_precision',
                'estimated_age_months',
                'estimated_age_recorded_at',
                'birthday_celebration_month',
                'birthday_celebration_day',
                'life_stage_override',
                'life_stage_override_by_user_id',
                'life_stage_override_at',
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
                'breedOrigins' => fn ($query) => $query->select([
                    'id',
                    'origin_key',
                    'pet_profile_id',
                    'breed_name',
                    'confidence',
                    'source',
                    'approximate_share_percent',
                    'position',
                ]),
                'activeIdentifyingMarks' => fn ($query) => $query
                    ->select([
                        'id',
                        'mark_key',
                        'pet_profile_id',
                        'type',
                        'description',
                        'visibility',
                        'position',
                    ])
                    ->where('visibility', 'public'),
                'names' => fn ($query) => $query
                    ->select([
                        'id',
                        'pet_profile_id',
                        'name',
                        'type',
                        'locale',
                        'recorded_at',
                    ])
                    ->where('visibility', PetProfileNameVisibility::Public->value)
                    ->oldest('recorded_at'),
            ])
            ->findOrFail($this->profileId);
        abort_unless($this->gate->allows('view', $profile), 404);
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
            'alternative_names' => $profile->names
                ->map(static fn ($name): array => [
                    'id' => $name->id,
                    'name' => $name->name,
                    'type' => $name->type->label(),
                    'locale' => $name->locale,
                ])
                ->all(),
            'species' => $this->speciesLabels->for(
                $profile->species,
                $profile->species_confidence,
            ),
            'scientific_name' => $profile->taxon?->activeVersion?->scientific_name,
            'breed_origin' => $this->breedOrigins->for($profile),
            'age' => $this->ageLabels->for($profile),
            'celebration_day' => $this->ageLabels->celebrationFor($profile),
            'life_stage' => $this->lifeStages->for($profile),
            'size' => $this->sizeCategories->for($profile),
            'appearance' => $this->appearance->for($profile),
            'body_covering' => $this->bodyCovering->for($profile),
            'identifying_marks' => $this->identifyingMarks->publicFor($profile),
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
                'title' => $pet['name'],
                'activeSection' => 'pets',
            ]);
    }
}
