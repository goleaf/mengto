<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use Carbon\CarbonImmutable;
use Database\Factories\OrganizationMembershipFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable|null $expires_at
 * @property int $id
 * @property int|null $invited_by_user_id
 * @property CarbonImmutable|null $joined_at
 * @property int $lock_version
 * @property int $organization_id
 * @property CarbonImmutable|null $removed_at
 * @property int|null $removed_by_user_id
 * @property string|null $removal_reason_code
 * @property OrganizationRole $role
 * @property OrganizationMembershipStatus $status
 * @property int $user_id
 * @property-read Organization $organization
 * @property-read User $user
 */
final class OrganizationMembership extends Model
{
    /** @use HasFactory<OrganizationMembershipFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'invited_by_user_id',
        'role',
        'status',
        'joined_at',
        'expires_at',
        'removed_by_user_id',
        'removed_at',
        'removal_reason_code',
        'lock_version',
    ];

    protected $hidden = ['removal_reason_code'];

    protected $attributes = ['status' => 'active', 'lock_version' => 0];

    protected function casts(): array
    {
        return [
            'role' => OrganizationRole::class,
            'status' => OrganizationMembershipStatus::class,
            'joined_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'removed_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
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

    /** @return BelongsTo<User, $this> */
    public function removedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by_user_id');
    }

    /** @param Builder<OrganizationMembership> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', OrganizationMembershipStatus::Active->value)
            ->where(function (Builder $expiry): void {
                $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function isActive(): bool
    {
        return $this->status === OrganizationMembershipStatus::Active
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
