<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AdoptionApplicationStatus;
use App\Enums\AdoptionPlacementType;
use App\Models\AdoptionApplication;
use App\Models\AdoptionCase;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<AdoptionApplication>
 */
final class AdoptionApplicationFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'adoption_case_id' => AdoptionCase::factory(),
            'applicant_user_id' => User::factory(),
            'reviewer_user_id' => null,
            'idempotency_key' => (string) Str::uuid(),
            'placement_type' => AdoptionPlacementType::Adoption,
            'status' => AdoptionApplicationStatus::Submitted,
            'identity_status' => 'unverified',
            'message' => fake()->sentence(16),
            'private_profile' => [
                'experience' => 'I have cared for adult cats for several years.',
                'home_context' => 'A quiet rented apartment with written animal permission.',
                'household' => 'Two adults who have agreed to the adoption.',
                'other_animals' => 'No other animals currently live in the home.',
                'care_plan' => 'Daily play, indoor enrichment, and regular veterinary care.',
                'placement_reason' => 'The animal appears compatible with our home routine.',
                'transport_plan' => 'We can attend meetings and collect with a secure carrier.',
            ],
            'private_references' => null,
            'terms_accepted' => true,
            'privacy_accepted' => true,
            'reference_contact_consent' => false,
            'lock_version' => 1,
            'submitted_at' => now(),
        ];
    }

    public function foster(): static
    {
        return $this->state(fn (): array => [
            'placement_type' => AdoptionPlacementType::Foster,
            'status' => AdoptionApplicationStatus::FosterPlaced,
        ]);
    }

    public function adopted(): static
    {
        return $this->state(fn (): array => [
            'status' => AdoptionApplicationStatus::Adopted,
            'reviewed_at' => now()->subWeek(),
            'contracted_at' => now()->subDay(),
        ]);
    }

    public function followUp(): static
    {
        return $this->state(fn (): array => [
            'status' => AdoptionApplicationStatus::FollowUp,
            'reviewed_at' => now()->subWeeks(2),
            'contracted_at' => now()->subWeek(),
            'follow_up_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (): array => [
            'status' => AdoptionApplicationStatus::Closed,
            'closed_at' => now(),
        ]);
    }
}
