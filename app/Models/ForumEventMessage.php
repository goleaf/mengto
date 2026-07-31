<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventMessageAudience;
use Database\Factories\ForumEventMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ForumEventMessageAudience $audience
 * @property string $body
 * @property int $forum_event_id
 * @property int $id
 * @property string $idempotency_key
 * @property int|null $sender_user_id
 * @property string $stable_key
 * @property-read ForumEvent $event
 * @property-read User|null $sender
 */
final class ForumEventMessage extends Model
{
    /** @use HasFactory<ForumEventMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_event_id',
        'sender_user_id',
        'stable_key',
        'idempotency_key',
        'audience',
        'body',
    ];

    protected $hidden = [
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'audience' => ForumEventMessageAudience::class,
        ];
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
