<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CredentialStatus;
use App\Enums\CredentialType;
use App\Models\Credential;
use App\Models\ExpertProfile;

/** @extends ApplicationFactory<Credential> */
class CredentialFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'expert_profile_id' => ExpertProfile::factory(),
            'type' => CredentialType::License->value,
            'title' => 'Veterinary practice license',
            'issuer' => 'National professional authority',
            'region' => 'Lithuania',
            'jurisdiction' => 'LT',
            'number_last_four' => fake()->numerify('####'),
            'credential_identifier_hash' => hash('sha256', fake()->uuid()),
            'issued_at' => now()->subYears(3),
            'expires_at' => now()->addYear(),
            'renewal_due_at' => now()->addMonths(10),
            'status' => CredentialStatus::Verified,
            'file_path' => 'credentials/private/'.fake()->uuid().'.pdf',
            'reviewed_by' => 'Verification team',
            'reviewer_user_id' => null,
            'verified_at' => now()->subMonth(),
            'public_summary_translation_key' => 'credential_verification.reason.approved',
            'scope' => ['veterinary-medicine'],
            'verification_notes' => ['scope' => 'veterinary-medicine'],
            'metadata' => [],
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (): array => [
            'status' => CredentialStatus::Submitted,
            'reviewed_by' => null,
            'reviewer_user_id' => null,
            'verified_at' => null,
            'public_summary_translation_key' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => CredentialStatus::Verified,
            'expires_at' => now()->subDay(),
            'renewal_due_at' => now()->subMonth(),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => CredentialStatus::Suspended,
            'suspended_at' => now(),
        ]);
    }
}
