<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SocialRelationshipDirection;
use App\Enums\SocialRelationshipType;
use App\Enums\SocialRequestStatus;
use Carbon\CarbonImmutable;
use Database\Factories\SocialRelationshipRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $active_key
 * @property int|null $created_by_user_id
 * @property int|null $decided_by_user_id
 * @property CarbonImmutable|null $decided_at
 * @property CarbonImmutable|null $delivered_at
 * @property SocialRelationshipDirection $direction
 * @property CarbonImmutable|null $expires_at
 * @property int $id
 * @property string $idempotency_key
 * @property int $lock_version
 * @property array<string, mixed>|null $metadata
 * @property string|null $message
 * @property string|null $message_fingerprint
 * @property bool $prevent_repeats
 * @property CarbonImmutable|null $repeat_after
 * @property string $request_key
 * @property SocialRelationshipType $relationship_type
 * @property string $risk_level
 * @property list<string>|null $risk_signals
 * @property CarbonImmutable $sent_at
 * @property int $source_actor_id
 * @property SocialRequestStatus $status
 * @property int $target_actor_id
 * @property-read SocialActor $sourceActor
 * @property-read SocialActor $targetActor
 */
final class SocialRelationshipRequest extends Model
{
    /** @use HasFactory<SocialRelationshipRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'request_key',
        'source_actor_id',
        'target_actor_id',
        'relationship_type',
        'direction',
        'status',
        'active_key',
        'idempotency_key',
        'created_by_user_id',
        'decided_by_user_id',
        'context_type',
        'context_key',
        'message',
        'message_fingerprint',
        'risk_level',
        'risk_signals',
        'reason_code',
        'lock_version',
        'metadata',
        'sent_at',
        'delivered_at',
        'decided_at',
        'expires_at',
        'repeat_after',
        'prevent_repeats',
    ];

    protected $hidden = ['message'];

    protected function casts(): array
    {
        return [
            'relationship_type' => SocialRelationshipType::class,
            'direction' => SocialRelationshipDirection::class,
            'status' => SocialRequestStatus::class,
            'message' => 'encrypted',
            'metadata' => 'array',
            'risk_signals' => 'array',
            'prevent_repeats' => 'boolean',
            'lock_version' => 'integer',
            'sent_at' => 'immutable_datetime',
            'delivered_at' => 'immutable_datetime',
            'decided_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'repeat_after' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'request_key';
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

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }

    /** @return HasMany<SocialRelationshipEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(SocialRelationshipEvent::class, 'social_relationship_request_id');
    }

    /** @return HasMany<SocialRelationship, $this> */
    public function relationships(): HasMany
    {
        return $this->hasMany(SocialRelationship::class, 'request_id');
    }

    /**
     * @param  Builder<SocialRelationshipRequest>  $query
     * @return Builder<SocialRelationshipRequest>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [
                SocialRequestStatus::Sent->value,
                SocialRequestStatus::Delivered->value,
                SocialRequestStatus::Pending->value,
            ])
            ->where(function (Builder $expiryQuery): void {
                $expiryQuery
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
