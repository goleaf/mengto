<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlaceManagementClaimEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $byte_size
 * @property string $checksum_sha256
 * @property CarbonImmutable $created_at
 * @property string $evidence_type
 * @property CarbonImmutable|null $expires_at
 * @property int $id
 * @property CarbonImmutable|null $issued_at
 * @property string $mime_type
 * @property string $original_name
 * @property string $private_disk
 * @property string $private_path
 * @property int $place_management_claim_id
 * @property string $stable_key
 * @property int $uploaded_by_user_id
 * @property string $upload_idempotency_key
 * @property string $upload_payload_fingerprint
 */
final class PlaceManagementClaimEvidence extends Model
{
    /** @use HasFactory<PlaceManagementClaimEvidenceFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'place_management_claim_evidence';

    protected $fillable = [
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
        'upload_idempotency_key',
        'upload_payload_fingerprint',
        'created_at',
    ];

    protected $hidden = [
        'private_disk',
        'private_path',
        'original_name',
        'checksum_sha256',
        'upload_idempotency_key',
        'upload_payload_fingerprint',
    ];

    protected $attributes = ['private_disk' => 'local'];

    protected function casts(): array
    {
        return [
            'original_name' => 'encrypted',
            'byte_size' => 'integer',
            'issued_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<PlaceManagementClaim, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(PlaceManagementClaim::class, 'place_management_claim_id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
