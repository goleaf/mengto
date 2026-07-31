<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use Carbon\CarbonImmutable;
use Database\Factories\ForumEventUpdateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ForumEventUpdateAudience $audience
 * @property int|null $author_user_id
 * @property string $body
 * @property int $forum_event_id
 * @property int $id
 * @property string $idempotency_key
 * @property CarbonImmutable $published_at
 * @property string $stable_key
 * @property string $title
 * @property ForumEventUpdateType $type
 * @property-read User|null $author
 * @property-read ForumEvent $event
 */
final class ForumEventUpdate extends Model
{
    /** @use HasFactory<ForumEventUpdateFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_event_id',
        'author_user_id',
        'stable_key',
        'idempotency_key',
        'type',
        'audience',
        'title',
        'body',
        'published_at',
    ];

    protected $hidden = [
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'type' => ForumEventUpdateType::class,
            'audience' => ForumEventUpdateAudience::class,
            'published_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
