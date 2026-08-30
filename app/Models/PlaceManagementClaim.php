<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceManagementClaimStatus;
use App\Enums\PlaceManagementClaimPurpose;
use App\Enums\PlaceManagementRole;
use App\Enums\PlaceVerificationMethod;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceManagementClaimFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string|null $active_conflict_key
 * @property int $claimant_user_id
 * @property string|null $contact_details
 * @property string|null $decision_detail
 * @property string|null $decision_reason_code
 * @property CarbonImmutable|null $decided_at
 * @property CarbonImmutable|null $evidence_expires_at
 * @property CarbonImmutable|null $expires_at
 * @property int $id
 * @property int $lock_version
 * @property int $place_id
 * @property PlaceManagementClaimPurpose $claim_purpose
 * @property int|null $predecessor_claim_id
 * @property int|null $represented_organization_id
 * @property PlaceManagementRole $requested_role
 * @property int|null $reviewer_user_id
 * @property CarbonImmutable|null $revoked_at
 * @property int|null $revoked_by_user_id
 * @property string|null $revocation_reason_code
 * @property CarbonImmutable|null $review_started_at
 * @property string $stable_key
 * @property PlaceManagementClaimStatus $status
 * @property string $submission_idempotency_key
 * @property string $submission_payload_fingerprint
 * @property CarbonImmutable $submitted_at
 * @property int|null $superseded_by_claim_id
 * @property int|null $target_user_id
 * @property PlaceVerificationMethod $verification_method
 * @property-read PlaceManagerAuthority|null $authority
 * @property-read User $claimant
 * @property-read Collection<int, PlaceManagementClaimEvidence> $evidence
 * @property-read Collection<int, PlaceManagementClaimEvent> $events
 * @property-read Place $place
 * @property-read Organization|null $representedOrganization
 * @property-read User|null $reviewer
 * @property-read Collection<int, PlaceManagementClaimScope> $requestedScopes
 */
final class PlaceManagementClaim extends Model
{
    /** @use HasFactory<PlaceManagementClaimFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'place_id',
        'claimant_user_id',
        'represented_organization_id',
        'predecessor_claim_id',
        'target_user_id',
        'claim_purpose',
        'requested_role',
        'verification_method',
        'contact_details',
        'status',
        'reviewer_user_id',
        'decision_reason_code',
        'decision_detail',
        'submitted_at',
        'review_started_at',
        'decided_at',
        'evidence_expires_at',
        'expires_at',
        'revoked_by_user_id',
        'revoked_at',
        'revocation_reason_code',
        'superseded_by_claim_id',
        'active_conflict_key',
        'submission_idempotency_key',
        'submission_payload_fingerprint',
        'lock_version',
    ];

    protected $hidden = [
        'active_conflict_key',
        'contact_details',
        'decision_detail',
        'submission_idempotency_key',
        'submission_payload_fingerprint',
    ];

    protected $attributes = [
        'status' => 'pending',
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'requested_role' => PlaceManagementRole::class,
            'claim_purpose' => PlaceManagementClaimPurpose::class,
            'verification_method' => PlaceVerificationMethod::class,
            'contact_details' => 'encrypted',
            'status' => PlaceManagementClaimStatus::class,
            'decision_detail' => 'encrypted',
            'submitted_at' => 'immutable_datetime',
            'review_started_at' => 'immutable_datetime',
            'decided_at' => 'immutable_datetime',
            'evidence_expires_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<User, $this> */
    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_user_id');
    }

    /** @return BelongsTo<Organization, $this> */
    public function representedOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'represented_organization_id');
    }

    /** @return BelongsTo<PlaceManagementClaim, $this> */
    public function predecessor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'predecessor_claim_id');
    }

    /** @return BelongsTo<User, $this> */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    /** @return BelongsTo<PlaceManagementClaim, $this> */
    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'superseded_by_claim_id');
    }

    /** @return HasMany<PlaceManagementClaimScope, $this> */
    public function requestedScopes(): HasMany
    {
        return $this->hasMany(PlaceManagementClaimScope::class);
    }

    /** @return HasMany<PlaceManagementClaimEvidence, $this> */
    public function evidence(): HasMany
    {
        return $this->hasMany(PlaceManagementClaimEvidence::class);
    }

    /** @return HasMany<PlaceManagementClaimEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(PlaceManagementClaimEvent::class);
    }

    /** @return HasOne<PlaceManagerAuthority, $this> */
    public function authority(): HasOne
    {
        return $this->hasOne(PlaceManagerAuthority::class, 'source_claim_id');
    }

    /** @return HasMany<PlaceManagementReviewerRecusal, $this> */
    public function reviewerRecusals(): HasMany
    {
        return $this->hasMany(PlaceManagementReviewerRecusal::class);
    }

    /** @param Builder<PlaceManagementClaim> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            PlaceManagementClaimStatus::Pending->value,
            PlaceManagementClaimStatus::NeedsInformation->value,
            PlaceManagementClaimStatus::UnderReview->value,
            PlaceManagementClaimStatus::Approved->value,
        ]);
    }
}
