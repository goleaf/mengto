<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PetBreedConfidence;
use App\Enums\PetBreedSource;
use App\Models\PetProfile;
use App\Models\PetProfileBreedOrigin;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<PetProfileBreedOrigin>
 */
final class PetProfileBreedOriginFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'origin_key' => Str::lower((string) Str::ulid()),
            'pet_profile_id' => PetProfile::factory(),
            'domestic_classification_id' => null,
            'breed_name' => fake()->words(2, true),
            'confidence' => PetBreedConfidence::OwnerReported,
            'source' => PetBreedSource::Unknown,
            'approximate_share_percent' => null,
            'position' => 0,
        ];
    }
}
