<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SocialRelationshipDirection;
use App\Enums\SocialRelationshipStatus;
use App\Enums\SocialRelationshipType;
use Carbon\CarbonImmutable;
use Database\Factories\SocialRelationshipFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int|null $accepted_by_user_id
 * @property string|null $active_key
 * @property int|null $created_by_user_id
 * @property SocialRelationshipDirection $direction
 * @property CarbonImmutable|null $ended_at
 * @property CarbonImmutable|null $ends_at
 * @property int $id
 * @property int $lock_version
 * @property CarbonImmutable|null $paused_at
 * @property string|null $reason_code
 * @property string $relationship_key
 * @property SocialRelationshipType $relationship_type
 * @property int|null $request_id
 * @property array<string, mixed>|null $rights
 * @property int $source_actor_id
 * @property CarbonImmutable $started_at
 * @property SocialRelationshipStatus $status
 * @property int $target_actor_id
 * @property-read SocialActor $sourceActor
 * @property-read SocialActor $targetActor
 */
final class SocialRelationship extends Model
{
    /** @use HasFactory<SocialRelationshipFactory> */
    use HasFactory;

    protected $fillable = [
        'relationship_key',
        'source_actor_id',
        'target_actor_id',
        'request_id',
        'relationship_type',
        'direction',
        'status',
        'active_key',
        'visibility',
        'rights',
        'created_by_user_id',
        'accepted_by_user_id',
        'context_type',
        'context_key',
        'reason_code',
        'lock_version',
        'started_at',
        'paused_at',
        'ends_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'relationship_type' => SocialRelationshipType::class,
            'direction' => SocialRelationshipDirection::class,
            'status' => SocialRelationshipStatus::class,
            'rights' => 'array',
            'lock_version' => 'integer',
            'started_at' => 'immutable_datetime',
            'paused_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'ended_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'relationship_key';
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

    /** @return BelongsTo<SocialRelationshipRequest, $this> */
    public function request(): BelongsTo
    {
        return $this->belongsTo(SocialRelationshipRequest::class, 'request_id');
    }

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    /** @return HasMany<SocialRelationshipEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(SocialRelationshipEvent::class);
    }

    /**
     * @param  Builder<SocialRelationship>  $query
     * @return Builder<SocialRelationship>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', SocialRelationshipStatus::Active->value)
            ->where(function (Builder $expiryQuery): void {
                $expiryQuery
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }
}
