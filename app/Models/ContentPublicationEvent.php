<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentPublicationEventType;
use App\Enums\ContentPublicationStatus;
use Database\Factories\ContentPublicationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class ContentPublicationEvent extends Model
{
    /** @use HasFactory<ContentPublicationEventFactory> */
    use HasFactory;

    protected $fillable = [
        'content_publication_id',
        'actor_user_id',
        'represented_actor_id',
        'actor_key_snapshot',
        'representation_role',
        'event_type',
        'from_status',
        'to_status',
        'idempotency_key',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => ContentPublicationEventType::class,
            'from_status' => ContentPublicationStatus::class,
            'to_status' => ContentPublicationStatus::class,
            'metadata' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException(
            'Content publication events are append-only.',
        ));
        self::deleting(static fn (): never => throw new LogicException(
            'Content publication events are append-only.',
        ));
    }

    /** @return BelongsTo<ContentPublication, $this> */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(ContentPublication::class, 'content_publication_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** @return BelongsTo<SocialActor, $this> */
    public function representedActor(): BelongsTo
    {
        return $this->belongsTo(SocialActor::class, 'represented_actor_id');
    }
}
