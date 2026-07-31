<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumPollVoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property list<int> $choices
 * @property int $forum_poll_id
 * @property int $id
 * @property string $idempotency_key
 * @property int $lock_version
 * @property int $user_id
 * @property-read ForumPoll $poll
 * @property-read User $user
 */
final class ForumPollVote extends Model
{
    /** @use HasFactory<ForumPollVoteFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_poll_id',
        'user_id',
        'choices',
        'idempotency_key',
        'lock_version',
    ];

    protected $attributes = [
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'choices' => 'array',
            'lock_version' => 'integer',
        ];
    }

    /** @return BelongsTo<ForumPoll, $this> */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(ForumPoll::class, 'forum_poll_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
