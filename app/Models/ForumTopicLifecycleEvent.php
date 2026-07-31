<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumTopicLifecycleEventType;
use Database\Factories\ForumTopicLifecycleEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * @property int|null $actor_user_id
 * @property Carbon|null $created_at
 * @property ForumTopicLifecycleEventType $event_type
 * @property string|null $from_status
 * @property int $forum_topic_id
 * @property int $id
 * @property string|null $idempotency_key
 * @property int $lock_version
 * @property array<array-key, mixed>|null $metadata
 * @property Carbon $occurred_at
 * @property string $reason_code
 * @property string|null $reason_translation_key
 * @property string|null $to_status
 * @property Carbon|null $updated_at
 */
final class ForumTopicLifecycleEvent extends Model
{
    /** @use HasFactory<ForumTopicLifecycleEventFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_topic_id',
        'actor_user_id',
        'event_type',
        'from_status',
        'to_status',
        'reason_code',
        'reason_translation_key',
        'lock_version',
        'idempotency_key',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => ForumTopicLifecycleEventType::class,
            'lock_version' => 'integer',
            'metadata' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('Forum topic lifecycle events are immutable.');
        });
        self::deleting(static function (): never {
            throw new LogicException('Forum topic lifecycle events are append-only.');
        });
    }

    /** @return BelongsTo<ForumTopic, $this> */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'forum_topic_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
