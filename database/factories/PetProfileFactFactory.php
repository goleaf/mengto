<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PetProfile;
use App\Models\PetProfileFact;
use App\Models\User;

/** @extends ApplicationFactory<PetProfileFact> */
final class PetProfileFactFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $factKey = fake()->randomElement(['birth-date', 'breed', 'microchip-status']);

        return [
            'pet_profile_id' => PetProfile::factory(),
            'fact_key' => $factKey,
            'value' => ['value' => fake()->word()],
            'precision' => 'unknown',
            'source_type' => 'owner',
            'author_user_id' => User::factory(),
            'verification_status' => 'unverified',
            'visibility' => 'private',
            'is_current' => true,
            'current_key' => null,
            'recorded_at' => now(),
            'metadata' => [],
        ];
    }
}
