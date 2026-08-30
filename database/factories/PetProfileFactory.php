<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PetBirthDatePrecision;
use App\Enums\PetProfileStatus;
use App\Enums\PetSizeCategory;
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
            'size_category' => null,
            'birth_date' => now()->subYears(fake()->numberBetween(1, 12))->toDateString(),
            'visibility' => 'public',
            'status' => PetProfileStatus::Active,
            'birth_date_precision' => PetBirthDatePrecision::Exact,
            'estimated_age_months' => null,
            'estimated_age_recorded_at' => null,
            'birthday_celebration_month' => null,
            'birthday_celebration_day' => null,
            'life_stage_override' => null,
            'life_stage_override_by_user_id' => null,
            'life_stage_override_at' => null,
            'sex' => 'unknown',
            'reproductive_status' => 'unknown',
            'is_discoverable' => true,
            'allow_external_indexing' => false,
            'lock_version' => 1,
            'state_entered_at' => now(),
            'published_at' => now(),
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

    public function withSize(PetSizeCategory $category): static
    {
        return $this->state(fn (): array => [
            'size_category' => $category,
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
        return $this->withStatus(PetProfileStatus::Draft);
    }

    public function active(): static
    {
        return $this->withStatus(PetProfileStatus::Active);
    }

    public function fosterCare(): static
    {
        return $this->withStatus(PetProfileStatus::FosterCare);
    }

    public function shelter(): static
    {
        return $this->withStatus(PetProfileStatus::Shelter);
    }

    public function seekingHome(): static
    {
        return $this->withStatus(PetProfileStatus::SeekingHome);
    }

    public function adoptionInProgress(): static
    {
        return $this->withStatus(PetProfileStatus::AdoptionInProgress);
    }

    public function transferred(): static
    {
        return $this->withStatus(PetProfileStatus::Transferred);
    }

    public function discoverable(): static
    {
        return $this->active()->state(fn (): array => ['visibility' => 'public']);
    }

    public function archived(): static
    {
        return $this->withStatus(PetProfileStatus::Archived);
    }

    public function lost(): static
    {
        return $this->withStatus(PetProfileStatus::Lost);
    }

    public function found(): static
    {
        return $this->withStatus(PetProfileStatus::Found);
    }

    public function identityUnverified(): static
    {
        return $this->withStatus(PetProfileStatus::IdentityUnverified);
    }

    public function disputedOwnership(): static
    {
        return $this->withStatus(PetProfileStatus::DisputedOwnership);
    }

    public function hidden(): static
    {
        return $this->withStatus(PetProfileStatus::Hidden);
    }

    public function memorial(): static
    {
        return $this->withStatus(PetProfileStatus::Memorial);
    }

    public function merged(?PetProfile $canonicalProfile = null): static
    {
        return $this->withStatus(PetProfileStatus::Merged)->state(fn (): array => [
            'canonical_profile_id' => $canonicalProfile ?? PetProfile::factory()->active(),
        ]);
    }

    public function deletionPending(): static
    {
        return $this->withStatus(PetProfileStatus::DeletionPending);
    }

    public function inactive(): static
    {
        return $this->archived();
    }

    private function withStatus(PetProfileStatus $status): static
    {
        return $this->state(function () use ($status): array {
            $now = now();
            $wasPublished = ! in_array($status, [
                PetProfileStatus::Draft,
                PetProfileStatus::IdentityUnverified,
            ], true);

            return [
                'status' => $status,
                'state_entered_at' => $now,
                'published_at' => $wasPublished ? $now->clone()->subDay() : null,
                'hidden_at' => $status === PetProfileStatus::Hidden ? $now : null,
                'archived_at' => $status === PetProfileStatus::Archived ? $now : null,
                'memorialized_at' => $status === PetProfileStatus::Memorial ? $now : null,
                'deletion_requested_at' => $status === PetProfileStatus::DeletionPending ? $now : null,
                'deletion_scheduled_for' => $status === PetProfileStatus::DeletionPending
                    ? $now->clone()->addDays((int) config('pet_profiles.deletion_grace_days', 30))
                    : null,
                'merged_at' => $status === PetProfileStatus::Merged ? $now : null,
                'is_discoverable' => $status->isPubliclyEligible(),
            ];
        });
    }
}
