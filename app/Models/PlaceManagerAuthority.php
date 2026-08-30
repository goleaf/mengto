<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceManagerAuthorityStatus;
use App\Enums\PlaceManagementRole;
use App\Enums\PlaceManagementScope;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceManagerAuthorityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $active_authority_key
 * @property string|null $end_reason_code
 * @property CarbonImmutable|null $ended_at
 * @property int|null $ended_by_user_id
 * @property CarbonImmutable|null $expires_at
 * @property int|null $granted_by_user_id
 * @property int|null $granted_to_user_id
 * @property int $id
 * @property int $lock_version
 * @property int $place_id
 * @property int|null $represented_organization_id
 * @property PlaceManagementRole $role
 * @property int $source_claim_id
 * @property string $stable_key
 * @property CarbonImmutable $starts_at
 * @property PlaceManagerAuthorityStatus $status
 * @property int|null $superseded_by_authority_id
 * @property-read Collection<int, PlaceManagerAuthorityScope> $scopes
 */
final class PlaceManagerAuthority extends Model
{
    /** @use HasFactory<PlaceManagerAuthorityFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'place_id',
        'source_claim_id',
        'granted_to_user_id',
        'represented_organization_id',
        'granted_by_user_id',
        'role',
        'status',
        'starts_at',
        'expires_at',
        'ended_by_user_id',
        'ended_at',
        'end_reason_code',
        'superseded_by_authority_id',
        'active_authority_key',
        'lock_version',
    ];

    protected $hidden = ['active_authority_key'];

    protected $attributes = ['status' => 'active', 'lock_version' => 0];

    protected function casts(): array
    {
        return [
            'role' => PlaceManagementRole::class,
            'status' => PlaceManagerAuthorityStatus::class,
            'starts_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'ended_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<PlaceManagementClaim, $this> */
    public function sourceClaim(): BelongsTo
    {
        return $this->belongsTo(PlaceManagementClaim::class, 'source_claim_id');
    }

    /** @return BelongsTo<User, $this> */
    public function grantedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_to_user_id');
    }

    /** @return BelongsTo<Organization, $this> */
    public function representedOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'represented_organization_id');
    }

    /** @return BelongsTo<User, $this> */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }

    /** @return HasMany<PlaceManagerAuthorityScope, $this> */
    public function scopes(): HasMany
    {
        return $this->hasMany(PlaceManagerAuthorityScope::class);
    }

    /** @param Builder<PlaceManagerAuthority> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', PlaceManagerAuthorityStatus::Active->value)
            ->whereNull('ended_at')
            ->where(function (Builder $expiry): void {
                $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function isActive(): bool
    {
        return $this->status === PlaceManagerAuthorityStatus::Active
            && $this->ended_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function hasScope(PlaceManagementScope $scope): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($this->relationLoaded('scopes')) {
            return $this->scopes->contains(
                static fn (PlaceManagerAuthorityScope $authorityScope): bool => $authorityScope->scope === $scope,
            );
        }

        return $this->scopes()->where('scope', $scope->value)->exists();
    }
}
