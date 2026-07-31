<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumGroupInvitationState;
use App\Enums\ForumGroupRole;
use Carbon\CarbonImmutable;
use Database\Factories\ForumGroupInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable $expires_at
 * @property int $forum_group_id
 * @property int $id
 * @property string $idempotency_key
 * @property int $invited_by_user_id
 * @property int $invited_user_id
 * @property string|null $message
 * @property string|null $open_key
 * @property CarbonImmutable|null $responded_at
 * @property ForumGroupRole $role
 * @property ForumGroupInvitationState $state
 * @property-read ForumGroup $group
 * @property-read User $invitee
 */
final class ForumGroupInvitation extends Model
{
    /** @use HasFactory<ForumGroupInvitationFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_group_id',
        'invited_user_id',
        'invited_by_user_id',
        'role',
        'state',
        'message',
        'open_key',
        'idempotency_key',
        'expires_at',
        'responded_at',
    ];

    protected $attributes = [
        'role' => 'member',
        'state' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'role' => ForumGroupRole::class,
            'state' => ForumGroupInvitationState::class,
            'expires_at' => 'immutable_datetime',
            'responded_at' => 'immutable_datetime',
        ];
    }

    public function hasExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /** @return BelongsTo<ForumGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'forum_group_id');
    }

    /** @return BelongsTo<User, $this> */
    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }
}
