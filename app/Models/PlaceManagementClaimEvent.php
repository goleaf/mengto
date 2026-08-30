<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceManagementClaimAction;
use App\Enums\PlaceManagementClaimStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceManagementClaimEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property PlaceManagementClaimAction $action
 * @property int|null $actor_user_id
 * @property array<string, string>|null $audit_context
 * @property CarbonImmutable $created_at
 * @property PlaceManagementClaimStatus|null $from_status
 * @property int $id
 * @property string $idempotency_key
 * @property string $payload_fingerprint
 * @property int $place_management_claim_id
 * @property int|null $place_manager_authority_id
 * @property string $reason_code
 * @property int $result_lock_version
 * @property int|null $expected_lock_version
 * @property PlaceManagementClaimStatus $to_status
 */
final class PlaceManagementClaimEvent extends Model
{
    /** @use HasFactory<PlaceManagementClaimEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'place_management_claim_id',
        'actor_user_id',
        'place_manager_authority_id',
        'action',
        'from_status',
        'to_status',
        'reason_code',
        'idempotency_key',
        'payload_fingerprint',
        'audit_context',
        'expected_lock_version',
        'result_lock_version',
        'created_at',
    ];

    protected $hidden = ['idempotency_key', 'payload_fingerprint', 'audit_context'];

    protected function casts(): array
    {
        return [
            'action' => PlaceManagementClaimAction::class,
            'from_status' => PlaceManagementClaimStatus::class,
            'to_status' => PlaceManagementClaimStatus::class,
            'audit_context' => 'encrypted:array',
            'expected_lock_version' => 'integer',
            'result_lock_version' => 'integer',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException('Place management claim events are immutable.'));
        self::deleting(static fn (): never => throw new LogicException('Place management claim events are immutable.'));
    }

    /** @return BelongsTo<PlaceManagementClaim, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(PlaceManagementClaim::class, 'place_management_claim_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** @return BelongsTo<PlaceManagerAuthority, $this> */
    public function authority(): BelongsTo
    {
        return $this->belongsTo(PlaceManagerAuthority::class, 'place_manager_authority_id');
    }
}
