<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumGroupEventType;
use Carbon\CarbonImmutable;
use Database\Factories\ForumGroupEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property int|null $actor_user_id
 * @property CarbonImmutable $created_at
 * @property ForumGroupEventType $event_type
 * @property int $forum_group_id
 * @property int $id
 * @property string $reason_code
 * @property int|null $subject_user_id
 * @property string $summary_translation_key
 */
final class ForumGroupEvent extends Model
{
    /** @use HasFactory<ForumGroupEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'forum_group_id',
        'actor_user_id',
        'subject_user_id',
        'event_type',
        'reason_code',
        'summary_translation_key',
        'metadata',
        'idempotency_key',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => ForumGroupEventType::class,
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException(
            'Forum group events are append-only.',
        ));
        self::deleting(static fn (): never => throw new LogicException(
            'Forum group events are append-only.',
        ));
    }

    /** @return BelongsTo<ForumGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'forum_group_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function subjectUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }
}
