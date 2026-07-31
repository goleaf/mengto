<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SocialAccountBlockStatus;
use Carbon\CarbonImmutable;
use Database\Factories\SocialAccountBlockFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $active_key
 * @property int $blocked_user_id
 * @property CarbonImmutable $blocked_at
 * @property int $blocker_user_id
 * @property string $block_key
 * @property int $created_by_user_id
 * @property int $id
 * @property string $idempotency_key
 * @property int $lock_version
 * @property string|null $reason_code
 * @property CarbonImmutable|null $revoked_at
 * @property int|null $revoked_by_user_id
 * @property string $scope
 * @property int|null $source_actor_id
 * @property SocialAccountBlockStatus $status
 * @property int|null $target_actor_id
 * @property-read User $blockedUser
 * @property-read User $blockerUser
 * @property-read SocialActor|null $sourceActor
 * @property-read SocialActor|null $targetActor
 */
final class SocialAccountBlock extends Model
{
    /** @use HasFactory<SocialAccountBlockFactory> */
    use HasFactory;

    protected $fillable = [
        'block_key',
        'blocker_user_id',
        'blocked_user_id',
        'source_actor_id',
        'target_actor_id',
        'status',
        'scope',
        'active_key',
        'idempotency_key',
        'reason_code',
        'lock_version',
        'created_by_user_id',
        'revoked_by_user_id',
        'blocked_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SocialAccountBlockStatus::class,
            'lock_version' => 'integer',
            'blocked_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'block_key';
    }

    /** @return BelongsTo<User, $this> */
    public function blockerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function blockedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }

    /** @return BelongsTo<SocialActor, $this> */
    public function sourceActor(): BelongsTo
    {
        return $this->belongsTo(SocialActor::class, 'source_actor_id');
    }

    /** @return BelongsTo<SocialActor, $this> */
    public function targetActor(): BelongsTo
    {
        return $this->belongsTo(SocialActor::class, 'target_actor_id');
    }

    /** @return HasMany<SocialRelationshipEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(SocialRelationshipEvent::class);
    }

    /**
     * @param  Builder<SocialAccountBlock>  $query
     * @return Builder<SocialAccountBlock>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SocialAccountBlockStatus::Active->value);
    }
}
