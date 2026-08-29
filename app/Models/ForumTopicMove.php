<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumTopicMoveFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int|null $actor_user_id
 * @property Carbon|null $created_at
 * @property int|null $from_forum_category_id
 * @property int $forum_topic_id
 * @property int $id
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $old_url
 * @property string $reason_code
 * @property int $to_forum_category_id
 * @property Carbon|null $updated_at
 */
final class ForumTopicMove extends Model
{
    /** @use HasFactory<ForumTopicMoveFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_topic_id',
        'from_forum_category_id',
        'to_forum_category_id',
        'actor_user_id',
        'reason_code',
        'old_url',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    /** @return BelongsTo<ForumTopic, $this> */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'forum_topic_id');
    }

    /** @return BelongsTo<ForumCategory, $this> */
    public function fromCategory(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'from_forum_category_id');
    }

    /** @return BelongsTo<ForumCategory, $this> */
    public function toCategory(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'to_forum_category_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
