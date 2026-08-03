<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PetBirthDatePrecision;
use App\Enums\PetProfileStatus;
use App\Enums\PetSpeciesConfidence;
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
            'species_confidence' => PetSpeciesConfidence::Confirmed,
            'breed' => fake()->words(2, true),
            'breed_origin_type' => null,
            'birth_date' => now()->subYears(fake()->numberBetween(1, 12))->toDateString(),
            'visibility' => 'public',
            'status' => PetProfileStatus::Active,
            'birth_date_precision' => PetBirthDatePrecision::Exact,
            'estimated_age_months' => null,
            'estimated_age_recorded_at' => null,
            'birthday_celebration_month' => null,
            'birthday_celebration_day' => null,
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
        return $this->state(fn (): array => [
            'visibility' => 'private',
            'is_discoverable' => false,
            'allow_external_indexing' => false,
        ]);
    }

    public function possibleSpecies(string $species = 'dog'): static
    {
        return $this->state(fn (): array => [
            'species' => in_array($species, ['cat', 'dog'], true) ? $species : 'dog',
            'species_confidence' => PetSpeciesConfidence::Possible,
        ]);
    }

    public function unidentifiedSpecies(): static
    {
        return $this->state(fn (): array => [
            'species' => 'unknown',
            'species_confidence' => PetSpeciesConfidence::Unidentified,
            'taxon_id' => null,
            'breed' => null,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => PetProfileStatus::Draft,
            'published_at' => null,
            'is_discoverable' => false,
        ]);
    }

    public function discoverable(): static
    {
        return $this->state(fn (): array => [
            'visibility' => 'public',
            'status' => PetProfileStatus::Active,
            'published_at' => now(),
            'is_discoverable' => true,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => PetProfileStatus::Archived,
            'archived_at' => now(),
            'is_discoverable' => false,
        ]);
    }

    public function lost(): static
    {
        return $this->state(fn (): array => [
            'status' => PetProfileStatus::Lost,
            'visibility' => 'public',
            'is_discoverable' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->archived();
    }
}
