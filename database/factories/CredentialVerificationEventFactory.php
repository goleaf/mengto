<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CredentialStatus;
use App\Models\Credential;
use App\Models\CredentialVerificationEvent;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<CredentialVerificationEvent> */
final class CredentialVerificationEventFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'credential_id' => Credential::factory(),
            'actor_user_id' => User::factory()->administrator(),
            'event_type' => 'status-changed',
            'from_status' => CredentialStatus::Submitted,
            'to_status' => CredentialStatus::Verified,
            'reason_translation_key' => 'credential_verification.reason.approved',
            'internal_reason' => 'The supplied evidence matched the issuing authority record.',
            'idempotency_key' => (string) Str::uuid(),
            'metadata' => [],
        ];
    }
}
