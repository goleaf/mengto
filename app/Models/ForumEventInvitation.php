<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventInvitationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ForumEventInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable $expires_at
 * @property string|null $active_pair_key
 * @property int $forum_event_id
 * @property int $id
 * @property string $idempotency_key
 * @property int|null $invited_by_user_id
 * @property int $invited_user_id
 * @property CarbonImmutable|null $responded_at
 * @property string|null $request_checksum
 * @property string $stable_key
 * @property ForumEventInvitationStatus $status
 * @property-read ForumEvent $event
 * @property-read User|null $inviter
 * @property-read User $recipient
 */
final class ForumEventInvitation extends Model
{
    /** @use HasFactory<ForumEventInvitationFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_event_id',
        'invited_by_user_id',
        'invited_user_id',
        'stable_key',
        'idempotency_key',
        'active_pair_key',
        'request_checksum',
        'status',
        'expires_at',
        'responded_at',
    ];

    protected $hidden = [
        'idempotency_key',
        'active_pair_key',
        'request_checksum',
    ];

    protected function casts(): array
    {
        return [
            'status' => ForumEventInvitationStatus::class,
            'expires_at' => 'immutable_datetime',
            'responded_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    public function isCurrent(): bool
    {
        return $this->status === ForumEventInvitationStatus::Pending
            && $this->expires_at->isFuture();
    }
}
