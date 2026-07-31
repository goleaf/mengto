<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use Carbon\CarbonImmutable;
use Database\Factories\ForumGroupMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property list<string>|array<string, string> $answers
 * @property CarbonImmutable|null $ended_at
 * @property int $forum_group_id
 * @property int $id
 * @property CarbonImmutable|null $joined_at
 * @property string|null $last_idempotency_key
 * @property int $lock_version
 * @property string $notification_level
 * @property CarbonImmutable|null $requested_at
 * @property string|null $restriction_reason
 * @property CarbonImmutable|null $reviewed_at
 * @property int|null $reviewed_by_user_id
 * @property string|null $review_reason
 * @property ForumGroupRole $role
 * @property ForumGroupMembershipState $state
 * @property int $user_id
 * @property-read ForumGroup $group
 * @property-read User $user
 */
final class ForumGroupMembership extends Model
{
    /** @use HasFactory<ForumGroupMembershipFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_group_id',
        'user_id',
        'role',
        'state',
        'notification_level',
        'answers',
        'reviewed_by_user_id',
        'review_reason',
        'restriction_reason',
        'requested_at',
        'reviewed_at',
        'joined_at',
        'ended_at',
        'lock_version',
        'last_idempotency_key',
    ];

    protected $attributes = [
        'role' => 'member',
        'notification_level' => 'important',
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'role' => ForumGroupRole::class,
            'state' => ForumGroupMembershipState::class,
            'answers' => 'array',
            'requested_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'joined_at' => 'immutable_datetime',
            'ended_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return $this->state === ForumGroupMembershipState::Active;
    }

    /** @return BelongsTo<ForumGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'forum_group_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
