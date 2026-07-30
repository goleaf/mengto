<?php

namespace App\Models;

use App\Enums\ForumSubscriptionLevel;
use Database\Factories\ForumEngagementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }
}
