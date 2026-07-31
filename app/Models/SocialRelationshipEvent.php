<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SocialRelationshipType;
use Database\Factories\SocialRelationshipEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property int|null $actor_user_id
 * @property string $actor_key_snapshot
 * @property string $event_type
 * @property int $id
 * @property string $idempotency_key
 * @property SocialRelationshipType $relationship_type
 * @property int|null $represented_actor_id
 * @property int|null $social_account_block_id
 * @property int|null $social_relationship_id
 * @property int|null $social_relationship_request_id
 * @property int $source_actor_id
 * @property int $target_actor_id
 */
final class SocialRelationshipEvent extends Model
{
    /** @use HasFactory<SocialRelationshipEventFactory> */
    use HasFactory;

    protected $fillable = [
        'social_relationship_id',
        'social_relationship_request_id',
        'social_account_block_id',
        'source_actor_id',
        'target_actor_id',
        'represented_actor_id',
        'actor_user_id',
        'actor_key_snapshot',
        'event_type',
        'relationship_type',
        'from_status',
        'to_status',
        'reason_code',
        'idempotency_key',
        'public_metadata',
        'private_metadata',
        'occurred_at',
    ];

    protected $hidden = ['private_metadata'];

    protected function casts(): array
    {
        return [
            'relationship_type' => SocialRelationshipType::class,
            'public_metadata' => 'array',
            'private_metadata' => 'encrypted:array',
            'occurred_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('Social relationship events are immutable.');
        });
        self::deleting(static function (): never {
            throw new LogicException('Social relationship events are append-only.');
        });
    }

    /** @return BelongsTo<SocialRelationship, $this> */
    public function relationship(): BelongsTo
    {
        return $this->belongsTo(SocialRelationship::class, 'social_relationship_id');
    }

    /** @return BelongsTo<SocialRelationshipRequest, $this> */
    public function request(): BelongsTo
    {
        return $this->belongsTo(SocialRelationshipRequest::class, 'social_relationship_request_id');
    }

    /** @return BelongsTo<SocialAccountBlock, $this> */
    public function accountBlock(): BelongsTo
    {
        return $this->belongsTo(SocialAccountBlock::class, 'social_account_block_id');
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

    /** @return BelongsTo<SocialActor, $this> */
    public function representedActor(): BelongsTo
    {
        return $this->belongsTo(SocialActor::class, 'represented_actor_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
