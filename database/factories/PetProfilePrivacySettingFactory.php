<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PetProfileVisibility;
use App\Models\PetProfile;
use App\Models\PetProfilePrivacySetting;

/** @extends ApplicationFactory<PetProfilePrivacySetting> */
final class PetProfilePrivacySettingFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'pet_profile_id' => PetProfile::factory(),
            'profile_visibility' => PetProfileVisibility::Private,
            'section_rules' => ['medical' => 'private', 'timeline' => 'connections'],
            'is_discoverable' => false,
            'allow_external_indexing' => false,
            'allow_direct_link' => false,
            'owner_display_mode' => 'contact-button',
            'manager_display_mode' => 'hidden',
            'public_location_precision' => 'hidden',
            'lock_version' => 1,
        ];
    }
}
