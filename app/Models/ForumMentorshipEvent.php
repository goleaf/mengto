<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumMentorshipEventType;
use App\Enums\ForumMentorshipState;
use Carbon\CarbonImmutable;
use Database\Factories\ForumMentorshipEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property int|null $actor_user_id
 * @property CarbonImmutable $created_at
 * @property ForumMentorshipEventType $event_type
 * @property int $forum_mentorship_id
 * @property ForumMentorshipState|null $from_state
 * @property int $id
 * @property string $reason_code
 * @property ForumMentorshipState|null $to_state
 */
final class ForumMentorshipEvent extends Model
{
    /** @use HasFactory<ForumMentorshipEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'forum_mentorship_id',
        'actor_user_id',
        'event_type',
        'from_state',
        'to_state',
        'reason_code',
        'summary_translation_key',
        'metadata',
        'idempotency_key',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => ForumMentorshipEventType::class,
            'from_state' => ForumMentorshipState::class,
            'to_state' => ForumMentorshipState::class,
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException(
            'Forum mentorship events are append-only.',
        ));
        self::deleting(static fn (): never => throw new LogicException(
            'Forum mentorship events are append-only.',
        ));
    }

    /** @return BelongsTo<ForumMentorship, $this> */
    public function mentorship(): BelongsTo
    {
        return $this->belongsTo(ForumMentorship::class, 'forum_mentorship_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
