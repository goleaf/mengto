<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ForumMentorshipMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property string $body
 * @property CarbonImmutable $created_at
 * @property int $forum_mentorship_id
 * @property int $id
 * @property int $sender_user_id
 * @property-read ForumMentorship $mentorship
 * @property-read User $sender
 */
final class ForumMentorshipMessage extends Model
{
    /** @use HasFactory<ForumMentorshipMessageFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'forum_mentorship_id',
        'sender_user_id',
        'body',
        'idempotency_key',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException(
            'Forum mentorship messages are append-only.',
        ));
        self::deleting(static fn (): never => throw new LogicException(
            'Forum mentorship messages are append-only.',
        ));
    }

    /** @return BelongsTo<ForumMentorship, $this> */
    public function mentorship(): BelongsTo
    {
        return $this->belongsTo(ForumMentorship::class, 'forum_mentorship_id');
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
