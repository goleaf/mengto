<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventReviewStatus;
use Database\Factories\ForumEventReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $body
 * @property int $forum_event_id
 * @property int $id
 * @property string $idempotency_key
 * @property int $rating
 * @property int $reviewer_user_id
 * @property string $stable_key
 * @property ForumEventReviewStatus $status
 * @property string $title
 * @property-read ForumEvent $event
 * @property-read User $reviewer
 */
final class ForumEventReview extends Model
{
    /** @use HasFactory<ForumEventReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_event_id',
        'reviewer_user_id',
        'stable_key',
        'idempotency_key',
        'rating',
        'title',
        'body',
        'status',
    ];

    protected $hidden = [
        'idempotency_key',
    ];

    protected $attributes = [
        'status' => 'published',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'status' => ForumEventReviewStatus::class,
        ];
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
