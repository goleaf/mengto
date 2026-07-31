<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AdoptionCaseStatus;
use App\Enums\AdoptionProviderIdentityStatus;
use App\Enums\AdoptionProviderType;
use App\Models\AdoptionCase;
use App\Models\Credential;
use App\Models\Listing;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<AdoptionCase>
 */
final class AdoptionCaseFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory()->adoption(),
            'case_number' => 'ADP-'.Str::upper(Str::random(12)),
            'provider_type' => AdoptionProviderType::Organization,
            'provider_identity_status' => AdoptionProviderIdentityStatus::Unverified,
            'provider_verified' => false,
            'status' => AdoptionCaseStatus::Published,
            'animal_name' => fake()->firstName(),
            'age_description' => 'Adult',
            'sex' => fake()->randomElement(['female', 'male', 'unknown']),
            'sterilization_status' => 'sterilized',
            'vaccination_status' => 'current',
            'microchip_status' => 'registered',
            'public_location' => 'Vilnius',
            'health_summary' => 'Routine veterinary records are available to screened applicants.',
            'behavior_summary' => 'Calm indoors and comfortable with predictable routines.',
            'compatibility_summary' => 'Gradual introductions to other animals are required.',
            'special_requirements' => 'Indoor home and follow-up contact during the first month.',
            'adoption_fee_minor' => 0,
            'currency' => 'EUR',
            'fee_explanation' => 'No adoption fee is requested.',
            'transport_options' => ['meetup'],
            'privacy_level' => 'approximate-location',
            'lock_version' => 1,
            'published_at' => now(),
        ];
    }

    public function foster(): static
    {
        return $this->state(fn (): array => [
            'status' => AdoptionCaseStatus::Fostered,
            'special_requirements' => 'Temporary foster placement with weekly coordinator contact.',
        ]);
    }

    public function withVerifiedProvider(Credential $credential): static
    {
        return $this->state(fn (): array => [
            'provider_expert_profile_id' => $credential->expert_profile_id,
            'provider_credential_id' => $credential->id,
            'provider_identity_status' => AdoptionProviderIdentityStatus::Verified,
            'provider_verified' => true,
            'provider_verified_at' => $credential->verified_at,
            'provider_verification_expires_at' => $credential->expires_at,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (): array => [
            'status' => AdoptionCaseStatus::Closed,
            'closed_at' => now(),
        ]);
    }
}
