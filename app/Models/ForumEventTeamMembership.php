<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventTeamMembershipStatus;
use App\Enums\ForumEventTeamRole;
use Carbon\CarbonImmutable;
use Database\Factories\ForumEventTeamMembershipFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable|null $ends_at
 * @property int $forum_event_id
 * @property int $id
 * @property int|null $invited_by_user_id
 * @property list<string>|null $permission_overrides
 * @property ForumEventTeamRole $role
 * @property CarbonImmutable|null $starts_at
 * @property ForumEventTeamMembershipStatus $status
 * @property int $user_id
 */
final class ForumEventTeamMembership extends Model
{
    /** @use HasFactory<ForumEventTeamMembershipFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_event_id',
        'user_id',
        'invited_by_user_id',
        'role',
        'status',
        'permission_overrides',
        'starts_at',
        'ends_at',
    ];

    protected $hidden = ['permission_overrides'];

    protected $attributes = ['status' => 'invited'];

    protected function casts(): array
    {
        return [
            'role' => ForumEventTeamRole::class,
            'status' => ForumEventTeamMembershipStatus::class,
            'permission_overrides' => 'array',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /** @param Builder<ForumEventTeamMembership> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', ForumEventTeamMembershipStatus::Active->value)
            ->where(function (Builder $range): void {
                $range->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $range): void {
                $range->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }
}
