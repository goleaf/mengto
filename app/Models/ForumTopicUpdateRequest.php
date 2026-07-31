<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumTopicUpdateRequestKind;
use App\Enums\ForumTopicUpdateRequestStatus;
use Database\Factories\ForumTopicUpdateRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property int $forum_topic_id
 * @property int $id
 * @property string $idempotency_key
 * @property ForumTopicUpdateRequestKind $kind
 * @property int $lock_version
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $proposed_body
 * @property string $reason
 * @property string|null $resolution_reason
 * @property Carbon|null $reviewed_at
 * @property int|null $reviewed_by_user_id
 * @property int|null $requester_user_id
 * @property ForumTopicUpdateRequestStatus $status
 * @property Carbon|null $updated_at
 */
final class ForumTopicUpdateRequest extends Model
{
    /** @use HasFactory<ForumTopicUpdateRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_topic_id',
        'requester_user_id',
        'kind',
        'status',
        'reason',
        'proposed_body',
        'reviewed_by_user_id',
        'reviewed_at',
        'resolution_reason',
        'lock_version',
        'idempotency_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'kind' => ForumTopicUpdateRequestKind::class,
            'status' => ForumTopicUpdateRequestStatus::class,
            'reviewed_at' => 'immutable_datetime',
            'lock_version' => 'integer',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<ForumTopic, $this> */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'forum_topic_id');
    }

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
