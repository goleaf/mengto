<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\PetProfileVisibility;
use App\Models\PetProfile;
use App\Models\PetProfilePrivacySetting;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class PetProfilePrivacyForm extends Form
{
    public string $profileVisibility = 'private';

    public string $locationVisibility = 'private';

    public string $postsVisibility = 'private';

    public string $friendsVisibility = 'private';

    public string $careVisibility = 'private';

    public string $activityVisibility = 'private';

    public bool $isDiscoverable = false;

    public bool $allowExternalIndexing = false;

    public bool $allowDirectLink = false;

    public string $ownerDisplayMode = 'contact-button';

    public string $managerDisplayMode = 'hidden';

    public string $publicLocationPrecision = 'hidden';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        $visibility = Rule::enum(PetProfileVisibility::class);

        return [
            'profileVisibility' => ['required', $visibility],
            'locationVisibility' => ['required', Rule::enum(PetProfileVisibility::class)],
            'postsVisibility' => ['required', Rule::enum(PetProfileVisibility::class)],
            'friendsVisibility' => ['required', Rule::enum(PetProfileVisibility::class)],
            'careVisibility' => ['required', Rule::enum(PetProfileVisibility::class)],
            'activityVisibility' => ['required', Rule::enum(PetProfileVisibility::class)],
            'isDiscoverable' => ['boolean'],
            'allowExternalIndexing' => ['boolean'],
            'allowDirectLink' => ['boolean'],
            'ownerDisplayMode' => [
                'required',
                Rule::in(['full', 'public-name', 'username', 'organization', 'contact-button', 'hidden']),
            ],
            'managerDisplayMode' => [
                'required',
                Rule::in(['all', 'primary', 'organization', 'count', 'hidden']),
            ],
            'publicLocationPrecision' => [
                'required',
                Rule::in(['country', 'city', 'district', 'region', 'hidden']),
            ],
        ];
    }

    public function fillFromProfile(PetProfile $profile): void
    {
        $settings = $profile->privacySetting;
        $legacyRules = data_get($profile->profile_data, 'privacy', []);
        $rules = is_array($legacyRules) ? $legacyRules : [];

        if ($settings instanceof PetProfilePrivacySetting) {
            $rules = $settings->section_rules ?? $rules;
            $this->profileVisibility = $settings->profile_visibility->value;
            $this->isDiscoverable = $settings->is_discoverable;
            $this->allowExternalIndexing = $settings->allow_external_indexing;
            $this->allowDirectLink = $settings->allow_direct_link;
            $this->ownerDisplayMode = $settings->owner_display_mode;
            $this->managerDisplayMode = $settings->manager_display_mode;
            $this->publicLocationPrecision = $settings->public_location_precision;
        } else {
            $this->profileVisibility = PetProfileVisibility::fromStored($profile->visibility)->value;
            $this->isDiscoverable = $profile->is_discoverable;
            $this->allowExternalIndexing = $profile->allow_external_indexing;
        }

        $this->locationVisibility = PetProfileVisibility::fromStored(
            is_string($rules['location'] ?? null) ? $rules['location'] : null,
        )->value;
        $this->postsVisibility = PetProfileVisibility::fromStored(
            is_string($rules['posts'] ?? null) ? $rules['posts'] : null,
        )->value;
        $this->friendsVisibility = PetProfileVisibility::fromStored(
            is_string($rules['friends'] ?? null) ? $rules['friends'] : null,
        )->value;
        $this->careVisibility = PetProfileVisibility::fromStored(
            is_string($rules['care'] ?? null) ? $rules['care'] : null,
        )->value;
        $this->activityVisibility = PetProfileVisibility::fromStored(
            is_string($rules['activity'] ?? null) ? $rules['activity'] : null,
        )->value;
    }

    /** @return array<string, mixed> */
    public function data(int $lockVersion, string $idempotencyKey): array
    {
        $validated = $this->validate();

        return [
            'profile_visibility' => $validated['profileVisibility'],
            'location_visibility' => $validated['locationVisibility'],
            'posts_visibility' => $validated['postsVisibility'],
            'friends_visibility' => $validated['friendsVisibility'],
            'care_visibility' => $validated['careVisibility'],
            'activity_visibility' => $validated['activityVisibility'],
            'is_discoverable' => $validated['isDiscoverable'],
            'allow_external_indexing' => $validated['allowExternalIndexing'],
            'allow_direct_link' => $validated['allowDirectLink'],
            'owner_display_mode' => $validated['ownerDisplayMode'],
            'manager_display_mode' => $validated['managerDisplayMode'],
            'public_location_precision' => $validated['publicLocationPrecision'],
            'lock_version' => $lockVersion,
            'idempotency_key' => $idempotencyKey,
        ];
    }
}
