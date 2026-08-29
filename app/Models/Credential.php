<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CredentialStatus;
use App\Enums\CredentialType;
use Database\Factories\CredentialFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property-read ExpertProfile|null $expertProfile
 * @property int $expert_profile_id
 * @property Carbon|null $expires_at
 * @property string|null $file_path
 * @property int $id
 * @property Carbon|null $issued_at
 * @property string $issuer
 * @property string|null $jurisdiction
 * @property int $lock_version
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $number_last_four
 * @property string|null $public_summary_translation_key
 * @property string|null $region
 * @property string|null $rejection_reason
 * @property int|null $replaces_credential_id
 * @property int|null $reviewer_user_id
 * @property string|null $reviewed_by
 * @property Carbon|null $renewal_due_at
 * @property array<array-key, mixed>|null $scope
 * @property CredentialStatus $status
 * @property Carbon|null $suspended_at
 * @property string $title
 * @property string $type
 * @property Carbon|null $updated_at
 * @property array<array-key, mixed>|null $verification_notes
 * @property Carbon|null $verified_at
 * @property Carbon|null $revoked_at
 * @property-read Collection<int, CredentialVerificationAppeal> $appeals
 * @property-read Collection<int, CredentialVerificationEvent> $verificationEvents
 * @property-read Credential|null $replacesCredential
 * @property-read User|null $reviewer
 */
class Credential extends Model
{
    /** @use HasFactory<CredentialFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id', 'type', 'title', 'issuer', 'region',
        'jurisdiction', 'number_last_four', 'credential_identifier_hash',
        'issued_at', 'expires_at', 'renewal_due_at', 'status', 'file_path',
        'public_summary_translation_key', 'scope', 'reviewed_by',
        'reviewer_user_id', 'replaces_credential_id', 'verified_at',
        'suspended_at', 'revoked_at', 'appeal_status', 'rejection_reason',
        'verification_notes', 'metadata', 'lock_version',
    ];

    protected $hidden = [
        'credential_identifier_hash',
        'file_path',
        'verification_notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => CredentialStatus::class,
            'issued_at' => 'date',
            'expires_at' => 'date',
            'renewal_due_at' => 'datetime',
            'verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'revoked_at' => 'datetime',
            'scope' => 'array',
            'verification_notes' => 'array',
            'metadata' => 'array',
            'lock_version' => 'integer',
        ];
    }

    /** @return BelongsTo<\App\Models\ExpertProfile, $this>*/
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    /** @return BelongsTo<Credential, $this> */
    public function replacesCredential(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_credential_id');
    }

    /** @return HasMany<CredentialVerificationEvent, $this> */
    public function verificationEvents(): HasMany
    {
        return $this->hasMany(CredentialVerificationEvent::class);
    }

    /** @return HasMany<CredentialVerificationAppeal, $this> */
    public function appeals(): HasMany
    {
        return $this->hasMany(CredentialVerificationAppeal::class);
    }

    /** @return HasMany<AdoptionCase, $this> */
    public function adoptionCases(): HasMany
    {
        return $this->hasMany(AdoptionCase::class, 'provider_credential_id');
    }

    /** @return HasMany<Credential, $this> */
    public function replacementCredentials(): HasMany
    {
        return $this->hasMany(self::class, 'replaces_credential_id');
    }

    public function effectiveStatus(): CredentialStatus
    {
        if (in_array($this->status, [CredentialStatus::Suspended, CredentialStatus::Revoked], true)) {
            return $this->status;
        }

        if (
            in_array($this->status, [CredentialStatus::Verified, CredentialStatus::Expiring], true)
            && $this->expires_at?->isPast()
        ) {
            return CredentialStatus::Expired;
        }

        if (
            $this->status === CredentialStatus::Verified
            && $this->renewal_due_at?->isPast()
        ) {
            return CredentialStatus::Expiring;
        }

        return $this->status;
    }

    public function credentialType(): ?CredentialType
    {
        return CredentialType::tryFrom($this->type);
    }
}
