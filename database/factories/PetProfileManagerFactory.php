<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PetEvidenceStatus;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\User;

/** @extends ApplicationFactory<PetProfileManager> */
final class PetProfileManagerFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'pet_profile_id' => PetProfile::factory(),
            'user_id' => User::factory(),
            'actor_key_snapshot' => fake()->unique()->slug(2),
            'role' => PetManagerRole::CoOwner,
            'status' => PetManagerStatus::Active,
            'permission_overrides' => null,
            'evidence_status' => PetEvidenceStatus::Unverified,
            'starts_at' => now(),
            'accepted_at' => now(),
            'lock_version' => 1,
            'metadata' => [],
        ];
    }

    public function invited(): static
    {
        return $this->state(fn (): array => [
            'status' => PetManagerStatus::Invited,
            'accepted_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => PetManagerStatus::Active,
            'ends_at' => now()->subMinute(),
        ]);
    }
}
