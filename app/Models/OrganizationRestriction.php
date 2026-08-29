<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationRestrictionCapability;
use Carbon\CarbonImmutable;
use Database\Factories\OrganizationRestrictionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $applied_by_user_id
 * @property OrganizationRestrictionCapability $capability
 * @property CarbonImmutable|null $ends_at
 * @property int $id
 * @property string $idempotency_key
 * @property int $organization_id
 * @property string $reason_code
 * @property CarbonImmutable|null $revoked_at
 * @property int|null $revoked_by_user_id
 * @property CarbonImmutable $starts_at
 * @property-read Organization $organization
 */
final class OrganizationRestriction extends Model
{
    /** @use HasFactory<OrganizationRestrictionFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'applied_by_user_id',
        'capability',
        'reason_code',
        'idempotency_key',
        'starts_at',
        'ends_at',
        'revoked_by_user_id',
        'revoked_at',
    ];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return [
            'capability' => OrganizationRestrictionCapability::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    /** @param Builder<OrganizationRestriction> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where('starts_at', '<=', now())
            ->where(function (Builder $expiry): void {
                $expiry->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null
            && $this->starts_at->isPast()
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }
}
