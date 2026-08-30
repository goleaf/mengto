<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceManagementClaim;
use App\Models\PlaceManagementClaimEvidence;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceManagementClaimEvidence> */
final class PlaceManagementClaimEvidenceFactory extends ApplicationFactory
{
    protected $model = PlaceManagementClaimEvidence::class;

    public function definition(): array
    {
        $key = 'place-evidence-'.Str::lower((string) Str::ulid());

        return [
            'place_management_claim_id' => PlaceManagementClaim::factory(),
            'uploaded_by_user_id' => User::factory(),
            'stable_key' => $key,
            'private_disk' => 'local',
            'private_path' => 'place-management-claims/factory/'.$key.'.pdf',
            'original_name' => 'verification.pdf',
            'mime_type' => 'application/pdf',
            'byte_size' => 1024,
            'checksum_sha256' => hash('sha256', $key),
            'evidence_type' => 'organization_document',
            'issued_at' => now()->subMonth(),
            'expires_at' => now()->addMonths(6),
            'upload_idempotency_key' => (string) Str::uuid(),
            'upload_payload_fingerprint' => hash('sha256', (string) Str::uuid()),
            'created_at' => now(),
        ];
    }
}
