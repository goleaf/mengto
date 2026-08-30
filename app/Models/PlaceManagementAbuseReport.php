<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable $created_at
 * @property string $details
 * @property int $id
 * @property string $idempotency_key
 * @property string $payload_fingerprint
 * @property int $place_id
 * @property int|null $place_management_claim_id
 * @property int|null $place_manager_authority_id
 * @property string $reason_code
 * @property int $reporter_user_id
 * @property string $stable_key
 * @property string $status
 */
final class PlaceManagementAbuseReport extends Model
{
    public const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'stable_key',
        'reporter_user_id',
        'place_id',
        'place_management_claim_id',
        'place_manager_authority_id',
        'reason_code',
        'details',
        'status',
        'idempotency_key',
        'payload_fingerprint',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['reporter_user_id', 'details', 'idempotency_key', 'payload_fingerprint'];

    protected $attributes = ['status' => 'open'];

    protected function casts(): array
    {
        return [
            'details' => 'encrypted',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<PlaceManagementClaim, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(PlaceManagementClaim::class, 'place_management_claim_id');
    }

    /** @return BelongsTo<PlaceManagerAuthority, $this> */
    public function authority(): BelongsTo
    {
        return $this->belongsTo(PlaceManagerAuthority::class, 'place_manager_authority_id');
    }
}
