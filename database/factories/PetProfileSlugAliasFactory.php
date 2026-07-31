<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PetProfile;
use App\Models\PetProfileSlugAlias;

/** @extends ApplicationFactory<PetProfileSlugAlias> */
final class PetProfileSlugAliasFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'pet_profile_id' => PetProfile::factory(),
            'slug' => fake()->unique()->slug(2),
            'source' => 'profile',
            'is_active' => true,
        ];
    }
}
