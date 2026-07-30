<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumSubscriptionLevel;
use Database\Factories\ForumEngagementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property int $id
 * @property bool $is_bookmarked
 * @property Carbon|null $last_read_at
 * @property Carbon|null $remind_at
 * @property ForumSubscriptionLevel $subscription_level
 * @property-read ForumTopic|null $topic
 * @property int $topic_id
 * @property Carbon|null $updated_at
 * @property string $user_key
 */
class ForumEngagement extends Model
{
    /** @use HasFactory<ForumEngagementFactory> */
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'user_key',
        'is_bookmarked',
        'subscription_level',
        'last_read_at',
        'remind_at',
    ];

    protected function casts(): array
    {
        return [
            'is_bookmarked' => 'boolean',
            'subscription_level' => ForumSubscriptionLevel::class,
            'last_read_at' => 'datetime',
            'remind_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\ForumTopic, $this>*/
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }
}
