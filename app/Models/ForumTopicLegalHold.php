<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumTopicLegalHoldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string|null $active_key
 * @property int|null $applied_by_user_id
 * @property Carbon|null $created_at
 * @property int $forum_topic_id
 * @property int $id
 * @property array<array-key, mixed>|null $metadata
 * @property string $private_reason
 * @property string $reason_code
 * @property string|null $release_reason
 * @property Carbon|null $released_at
 * @property int|null $released_by_user_id
 * @property Carbon|null $review_at
 * @property Carbon $starts_at
 * @property Carbon|null $updated_at
 */
final class ForumTopicLegalHold extends Model
{
    /** @use HasFactory<ForumTopicLegalHoldFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_topic_id',
        'applied_by_user_id',
        'reason_code',
        'private_reason',
        'starts_at',
        'review_at',
        'released_at',
        'released_by_user_id',
        'release_reason',
        'active_key',
        'metadata',
    ];

    protected $hidden = [
        'private_reason',
        'release_reason',
        'active_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'private_reason' => 'encrypted',
            'starts_at' => 'immutable_datetime',
            'review_at' => 'immutable_datetime',
            'released_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<ForumTopic, $this> */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'forum_topic_id');
    }

    /** @return BelongsTo<User, $this> */
    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by_user_id');
    }

    public function isActive(): bool
    {
        return $this->released_at === null;
    }
}
