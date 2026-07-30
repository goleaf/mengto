<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CredentialStatus;
use App\Models\Credential;
use App\Models\ExpertProfile;

/** @extends ApplicationFactory<Credential> */
class CredentialFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'expert_profile_id' => ExpertProfile::factory(),
            'type' => 'license',
            'title' => 'Veterinary practice license',
            'issuer' => 'National professional authority',
            'region' => 'Lithuania',
            'number_last_four' => fake()->numerify('####'),
            'issued_at' => now()->subYears(3),
            'expires_at' => now()->addYear(),
            'status' => CredentialStatus::Verified,
            'file_path' => 'credentials/private/'.fake()->uuid().'.pdf',
            'reviewed_by' => 'Verification team',
            'verified_at' => now()->subMonth(),
            'verification_notes' => ['scope' => 'veterinary-medicine'],
        ];
    }
}
