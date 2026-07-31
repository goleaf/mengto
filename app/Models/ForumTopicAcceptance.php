<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumTopicAcceptanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumTopicAcceptance extends Model
{
    /** @use HasFactory<ForumTopicAcceptanceFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_topic_id',
        'forum_answer_id',
        'accepted_by_user_id',
        'acceptance_type',
        'is_active',
        'accepted_at',
        'invalidated_at',
        'invalidation_reason_code',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'accepted_at' => 'immutable_datetime',
            'invalidated_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<ForumTopic, $this> */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'forum_topic_id');
    }

    /** @return BelongsTo<ForumAnswer, $this> */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(ForumAnswer::class, 'forum_answer_id');
    }

    /** @return BelongsTo<User, $this> */
    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }
}
