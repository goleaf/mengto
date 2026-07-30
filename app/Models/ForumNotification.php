<?php

namespace App\Models;

use Database\Factories\ForumNotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumNotification extends Model
{
    /** @use HasFactory<ForumNotificationFactory> */
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'user_key',
        'type',
        'title',
        'body',
        'deduplication_key',
        'read_at',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }
}
