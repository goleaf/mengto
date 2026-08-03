<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PetProfileNameType;
use App\Enums\PetProfileNameVisibility;
use App\Models\PetProfile;
use App\Models\PetProfileName;
use App\Models\User;
use App\Services\PetProfileNameNormalizer;

/** @extends ApplicationFactory<PetProfileName> */
final class PetProfileNameFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->firstName();

        return [
            'pet_profile_id' => PetProfile::factory(),
            'name' => $name,
            'normalized_name' => app(PetProfileNameNormalizer::class)->normalize($name),
            'type' => PetProfileNameType::Nickname,
            'visibility' => PetProfileNameVisibility::Private,
            'locale' => null,
            'is_searchable' => true,
            'recorded_by_user_id' => User::factory(),
            'recorded_at' => now(),
        ];
    }
}
