<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ForumEventHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $actor_user_id
 * @property CarbonImmutable $created_at
 * @property string $event_type
 * @property string|null $from_status
 * @property int $forum_event_id
 * @property int $id
 * @property string|null $idempotency_key
 * @property array<array-key, mixed>|null $metadata
 * @property string $reason_code
 * @property int|null $subject_user_id
 * @property string $summary_translation_key
 * @property string|null $to_status
 * @property-read User|null $actor
 * @property-read ForumEvent $event
 * @property-read User|null $subject
 */
final class ForumEventHistory extends Model
{
    /** @use HasFactory<ForumEventHistoryFactory> */
    use HasFactory;

    protected $table = 'forum_event_history';

    public $timestamps = false;

    protected $fillable = [
        'forum_event_id',
        'actor_user_id',
        'subject_user_id',
        'event_type',
        'from_status',
        'to_status',
        'reason_code',
        'summary_translation_key',
        'metadata',
        'idempotency_key',
        'created_at',
    ];

    protected $hidden = [
        'metadata',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }
}
