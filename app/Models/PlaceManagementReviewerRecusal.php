<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlaceManagementReviewerRecusalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable $created_at
 * @property int $id
 * @property string $idempotency_key
 * @property int $place_management_claim_id
 * @property string|null $private_note
 * @property string $reason_code
 * @property int $reviewer_user_id
 */
final class PlaceManagementReviewerRecusal extends Model
{
    /** @use HasFactory<PlaceManagementReviewerRecusalFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'place_management_claim_id',
        'reviewer_user_id',
        'reason_code',
        'private_note',
        'idempotency_key',
        'created_at',
    ];

    protected $hidden = ['private_note', 'idempotency_key'];

    protected function casts(): array
    {
        return [
            'private_note' => 'encrypted',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<PlaceManagementClaim, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(PlaceManagementClaim::class, 'place_management_claim_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
