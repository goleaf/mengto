<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumNotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string|null $body
 * @property Carbon|null $created_at
 * @property string $deduplication_key
 * @property int $id
 * @property Carbon|null $read_at
 * @property string $title
 * @property-read ForumTopic|null $topic
 * @property int|null $topic_id
 * @property string $type
 * @property Carbon|null $updated_at
 * @property string $user_key
 */
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

    /** @return BelongsTo<\App\Models\ForumTopic, $this>*/
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }
}
