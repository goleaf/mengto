<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PetProfileStatus;
use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<PetProfile>
 */
final class PetProfileFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $slug = Str::slug(fake()->unique()->words(2, true));

        return [
            'user_id' => User::factory(),
            'profile_key' => 'pet-'.Str::lower((string) Str::ulid()),
            'slug' => $slug,
            'name' => fake()->firstName(),
            'species' => fake()->randomElement(['dog', 'cat', 'rabbit', 'bird']),
            'breed' => fake()->words(2, true),
            'birth_date' => now()->subYears(fake()->numberBetween(1, 12))->toDateString(),
            'visibility' => 'public',
            'status' => PetProfileStatus::Active,
            'birth_date_precision' => 'exact',
            'sex' => 'unknown',
            'reproductive_status' => 'unknown',
            'is_discoverable' => true,
            'allow_external_indexing' => false,
            'lock_version' => 1,
            'state_entered_at' => now(),
            'profile_data' => [],
        ];
    }

    public function privateProfile(): static
    {
        return $this->state(fn (): array => ['visibility' => 'private']);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['status' => PetProfileStatus::Archived]);
    }
}
