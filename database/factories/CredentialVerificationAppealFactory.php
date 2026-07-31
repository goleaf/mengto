<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Credential;
use App\Models\CredentialVerificationAppeal;
use App\Models\User;

/** @extends ApplicationFactory<CredentialVerificationAppeal> */
final class CredentialVerificationAppealFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'credential_id' => Credential::factory()->suspended(),
            'submitted_by_user_id' => User::factory(),
            'reviewer_user_id' => null,
            'status' => 'submitted',
            'statement' => 'The issuing authority has corrected its record and supplied updated evidence.',
            'reviewer_response' => null,
            'reviewed_at' => null,
            'closed_at' => null,
            'metadata' => [],
        ];
    }
}
