<?php

declare(strict_types=1);

use App\Enums\PlaceManagementClaimStatus;
use App\Enums\PlaceManagementRole;
use App\Enums\PlaceManagementScope;
use App\Enums\PlaceVerificationMethod;
use App\Models\Place;
use App\Models\PlaceManagementClaim;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('place management claim schema stores the exact lifecycle and private relational evidence boundary', function () {
    expect(array_map(
        static fn (PlaceManagementClaimStatus $status): string => $status->value,
        PlaceManagementClaimStatus::cases(),
    ))->toBe([
        'pending',
        'needs_information',
        'under_review',
        'approved',
        'rejected',
        'expired',
        'revoked',
        'superseded',
    ])->and(Schema::hasTable('place_management_claims'))->toBeTrue()
        ->and(Schema::hasTable('place_management_claim_scopes'))->toBeTrue()
        ->and(Schema::hasTable('place_management_claim_evidence'))->toBeTrue()
        ->and(Schema::hasTable('place_management_claim_events'))->toBeTrue()
        ->and(Schema::hasTable('place_manager_authorities'))->toBeTrue()
        ->and(Schema::hasTable('place_manager_authority_scopes'))->toBeTrue()
        ->and(Schema::hasTable('place_management_reviewers'))->toBeTrue()
        ->and(Schema::hasTable('place_management_reviewer_recusals'))->toBeTrue()
        ->and(Schema::hasColumns('place_management_claims', [
            'place_id',
            'claimant_user_id',
            'represented_organization_id',
            'requested_role',
            'verification_method',
            'status',
            'reviewer_user_id',
            'decision_reason_code',
            'decision_detail',
            'evidence_expires_at',
            'expires_at',
            'revoked_at',
            'revoked_by_user_id',
            'superseded_by_claim_id',
            'active_conflict_key',
            'submission_idempotency_key',
            'lock_version',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('place_management_claim_evidence', [
            'place_management_claim_id',
            'uploaded_by_user_id',
            'stable_key',
            'private_disk',
            'private_path',
            'original_name',
            'mime_type',
            'byte_size',
            'checksum_sha256',
            'evidence_type',
            'issued_at',
            'expires_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('place_question_answers', [
            'place_manager_authority_id',
            'verification_scope',
            'verification_source',
        ]))->toBeTrue();

    $claimant = User::factory()->create();
    $place = Place::factory()->public()->create();
    $claim = PlaceManagementClaim::factory()->for($place)->for($claimant, 'claimant')->create([
        'requested_role' => PlaceManagementRole::Owner,
        'verification_method' => PlaceVerificationMethod::OrganizationDocument,
        'decision_detail' => 'Private reviewer reasoning',
    ]);
    $raw = DB::table('place_management_claims')->where('id', $claim->id)->firstOrFail();

    expect((string) $raw->decision_detail)->not->toContain('Private reviewer reasoning')
        ->and($claim->toArray())->not->toHaveKeys([
            'active_conflict_key',
            'submission_idempotency_key',
            'decision_detail',
        ])
        ->and(PlaceManagementScope::OfficialResponses->label())->toBeString();
});
