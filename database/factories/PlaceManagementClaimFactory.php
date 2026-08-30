<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceManagementClaimStatus;
use App\Enums\PlaceManagementClaimPurpose;
use App\Enums\PlaceManagementRole;
use App\Enums\PlaceVerificationMethod;
use App\Models\Place;
use App\Models\PlaceManagementClaim;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceManagementClaim> */
final class PlaceManagementClaimFactory extends ApplicationFactory
{
    protected $model = PlaceManagementClaim::class;

    public function definition(): array
    {
        return [
            'stable_key' => 'place-claim-'.Str::lower((string) Str::ulid()),
            'place_id' => Place::factory()->public(),
            'claimant_user_id' => User::factory(),
            'represented_organization_id' => null,
            'predecessor_claim_id' => null,
            'target_user_id' => null,
            'claim_purpose' => PlaceManagementClaimPurpose::Initial,
            'requested_role' => PlaceManagementRole::Owner,
            'verification_method' => PlaceVerificationMethod::OrganizationDocument,
            'contact_details' => fake()->safeEmail(),
            'status' => PlaceManagementClaimStatus::Pending,
            'reviewer_user_id' => null,
            'decision_reason_code' => null,
            'decision_detail' => null,
            'submitted_at' => now(),
            'review_started_at' => null,
            'decided_at' => null,
            'evidence_expires_at' => now()->addMonths(6),
            'expires_at' => now()->addDays(30),
            'revoked_by_user_id' => null,
            'revoked_at' => null,
            'revocation_reason_code' => null,
            'superseded_by_claim_id' => null,
            'active_conflict_key' => fn (array $attributes): string => hash('sha256', implode('|', [
                (string) $attributes['place_id'],
                (string) $attributes['claimant_user_id'],
                (string) ($attributes['represented_organization_id'] ?? 'personal'),
                $attributes['requested_role'] instanceof PlaceManagementRole
                    ? $attributes['requested_role']->value
                    : (string) $attributes['requested_role'],
            ])),
            'submission_idempotency_key' => (string) Str::uuid(),
            'submission_payload_fingerprint' => hash('sha256', (string) Str::uuid()),
            'lock_version' => 0,
        ];
    }
}
