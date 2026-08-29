<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ExpertProfileStatus;
use App\Enums\VerificationStatus;
use App\Models\ExpertProfile;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ExpertProfile> */
class ExpertProfileFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            'owner_key' => fake()->unique()->userName(),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 999),
            'public_name' => $name,
            'legal_name' => $name,
            'primary_type' => 'veterinarian',
            'headline' => 'Companion animal veterinarian',
            'bio' => fake()->paragraphs(2, true),
            'approach' => 'Clear explanations, careful triage, and referrals when a case is outside scope.',
            'boundaries' => 'Routine appointments only. Emergency cases should contact a clinic directly.',
            'years_experience' => 6,
            'country' => 'Lithuania',
            'city' => 'Vilnius',
            'service_area' => 'Vilnius',
            'specializations' => ['general-practice'],
            'species' => ['dog', 'cat'],
            'age_groups' => ['young', 'adult', 'senior'],
            'languages' => ['Lithuanian', 'English'],
            'formats' => ['in-person', 'video'],
            'methods' => [],
            'workplaces' => [],
            'accessibility' => ['parking'],
            'professional_interests' => ['preventive-care'],
            'availability_status' => 'available',
            'response_time' => 'Within one business day',
            'accepts_new_clients' => true,
            'offers_emergency_care' => false,
            'price_from' => 45,
            'currency' => 'EUR',
            'status' => ExpertProfileStatus::Published,
            'verification_status' => VerificationStatus::Verified,
            'identity_verified' => true,
            'education_verified' => true,
            'qualification_verified' => true,
            'license_verified' => true,
            'workplace_verified' => true,
            'organization_verified' => false,
            'contact_verified' => true,
            'verification_expires_at' => now()->addYear(),
            'next_available_at' => now()->addDay(),
            'avatar_url' => '/images/places/veterinary-primary-md.jpg',
            'review_average' => 0,
            'review_count' => 0,
            'verified_review_count' => 0,
            'forum_answer_count' => 0,
            'publication_count' => 0,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => [
            'verification_status' => VerificationStatus::Unsubmitted,
            'identity_verified' => false,
            'education_verified' => false,
            'qualification_verified' => false,
            'license_verified' => false,
            'workplace_verified' => false,
            'contact_verified' => false,
            'verification_expires_at' => null,
        ]);
    }
}
