<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceAccessGrantStatus;
use App\Enums\PlaceAccessPurpose;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceAccessGrantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $event_id
 * @property int $id
 * @property string $idempotency_key
 * @property bool $may_view_exact_location
 * @property int $place_id
 * @property PlaceAccessPurpose $purpose
 * @property CarbonImmutable|null $revoked_at
 * @property string|null $revocation_reason_code
 * @property PlaceAccessGrantStatus $status
 * @property int $user_id
 * @property CarbonImmutable $valid_from
 * @property CarbonImmutable $valid_until
 */
final class PlaceAccessGrant extends Model
{
    /** @use HasFactory<PlaceAccessGrantFactory> */
    use HasFactory;

    protected $fillable = [
        'place_id', 'user_id', 'event_id', 'issued_by_user_id', 'revoked_by_user_id',
        'purpose', 'status', 'may_view_exact_location', 'valid_from', 'valid_until',
        'revoked_at', 'revocation_reason_code', 'idempotency_key', 'metadata',
    ];

    protected $hidden = ['idempotency_key', 'metadata'];

    protected $attributes = ['status' => 'active', 'may_view_exact_location' => true];

    protected function casts(): array
    {
        return [
            'purpose' => PlaceAccessPurpose::class,
            'status' => PlaceAccessGrantStatus::class,
            'may_view_exact_location' => 'boolean',
            'valid_from' => 'immutable_datetime',
            'valid_until' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'metadata' => 'encrypted:array',
        ];
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'event_id');
    }

    /** @param Builder<PlaceAccessGrant> $query @return Builder<PlaceAccessGrant> */
    public function scopeActive(Builder $query): Builder
    {
        return self::constrainActiveAt($query, now());
    }

    public static function constrainActiveAt(Builder $query, Carbon $at): Builder
    {
        return $query
            ->where('status', PlaceAccessGrantStatus::Active->value)
            ->whereNull('revoked_at')
            ->where('valid_from', '<=', $at)
            ->where('valid_until', '>', $at);
    }

    public function isActive(): bool
    {
        return $this->status === PlaceAccessGrantStatus::Active
            && $this->revoked_at === null
            && $this->valid_from->lessThanOrEqualTo(now())
            && $this->valid_until->isFuture();
    }
}
