<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceManagementClaimAction;
use App\Enums\PlaceManagementClaimStatus;
use App\Models\PlaceManagementClaim;
use App\Models\PlaceManagementClaimEvent;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceManagementClaimEvent> */
final class PlaceManagementClaimEventFactory extends ApplicationFactory
{
    protected $model = PlaceManagementClaimEvent::class;

    public function definition(): array
    {
        return [
            'place_management_claim_id' => PlaceManagementClaim::factory(),
            'actor_user_id' => User::factory(),
            'place_manager_authority_id' => null,
            'action' => PlaceManagementClaimAction::Submitted,
            'from_status' => null,
            'to_status' => PlaceManagementClaimStatus::Pending,
            'reason_code' => 'claim-submitted',
            'idempotency_key' => (string) Str::uuid(),
            'payload_fingerprint' => hash('sha256', (string) Str::uuid()),
            'audit_context' => ['channel' => 'factory'],
            'expected_lock_version' => null,
            'result_lock_version' => 0,
            'created_at' => now(),
        ];
    }
}
