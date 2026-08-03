<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationRestrictionCapability;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\OrganizationType;
use App\Enums\OrganizationVerificationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CarbonImmutable|null $archived_at
 * @property string $creation_idempotency_key
 * @property string $default_locale
 * @property int $id
 * @property int $lock_version
 * @property array<string, mixed>|null $metadata
 * @property string $name
 * @property int $owner_user_id
 * @property string|null $public_region
 * @property string $slug
 * @property string $stable_key
 * @property OrganizationStatus $status
 * @property string|null $summary
 * @property CarbonImmutable|null $suspended_at
 * @property int|null $suspended_by_user_id
 * @property string|null $suspension_reason_code
 * @property OrganizationType $type
 * @property CarbonImmutable|null $verification_expires_at
 * @property string|null $verification_source
 * @property OrganizationVerificationStatus $verification_status
 * @property CarbonImmutable|null $verified_at
 * @property-read Collection<int, OrganizationAuditEvent> $auditEvents
 * @property-read Collection<int, ForumEvent> $events
 * @property-read Collection<int, OrganizationInvitation> $invitations
 * @property-read Collection<int, OrganizationMembership> $memberships
 * @property-read User $owner
 * @property-read Collection<int, OrganizationRestriction> $activeRestrictions
 * @property-read Collection<int, OrganizationRestriction> $restrictions
 */
final class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'stable_key',
        'slug',
        'creation_idempotency_key',
        'name',
        'summary',
        'type',
        'status',
        'verification_status',
        'verification_source',
        'verified_at',
        'verification_expires_at',
        'default_locale',
        'public_region',
        'lock_version',
        'suspended_by_user_id',
        'suspended_at',
        'suspension_reason_code',
        'metadata',
        'archived_at',
    ];

    protected $hidden = [
        'creation_idempotency_key',
        'metadata',
        'suspension_reason_code',
    ];

    protected $attributes = [
        'status' => 'active',
        'verification_status' => 'not_assessed',
        'default_locale' => 'en',
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'type' => OrganizationType::class,
            'status' => OrganizationStatus::class,
            'verification_status' => OrganizationVerificationStatus::class,
            'verified_at' => 'immutable_datetime',
            'verification_expires_at' => 'immutable_datetime',
            'lock_version' => 'integer',
            'suspended_at' => 'immutable_datetime',
            'metadata' => 'encrypted:array',
            'archived_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by_user_id');
    }

    /** @return HasMany<OrganizationMembership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(OrganizationMembership::class);
    }

    /** @return HasMany<OrganizationMembership, $this> */
    public function activeMemberships(): HasMany
    {
        $memberships = $this->memberships();
        $memberships->getQuery()->active();

        return $memberships;
    }

    /** @return HasMany<OrganizationInvitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    /** @return HasMany<OrganizationRestriction, $this> */
    public function restrictions(): HasMany
    {
        return $this->hasMany(OrganizationRestriction::class);
    }

    /** @return HasMany<OrganizationRestriction, $this> */
    public function activeRestrictions(): HasMany
    {
        $restrictions = $this->restrictions();
        $restrictions->getQuery()->active();

        return $restrictions;
    }

    /** @return HasMany<OrganizationAuditEvent, $this> */
    public function auditEvents(): HasMany
    {
        return $this->hasMany(OrganizationAuditEvent::class);
    }

    /** @return HasMany<ForumEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ForumEvent::class, 'responsible_organization_id');
    }

    /**
     * @param  Builder<Organization>  $query
     * @return Builder<Organization>
     */
    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdministrator()) {
            return $query;
        }

        return $query->whereHas('activeMemberships', function (Builder $memberships) use ($user): void {
            $memberships->where('user_id', $user->id);
        });
    }

    /**
     * @param  Builder<Organization>  $query
     * @return Builder<Organization>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', OrganizationStatus::Active->value)
            ->whereNull('archived_at');
    }

    /**
     * @param  Builder<Organization>  $query
     * @return Builder<Organization>
     */
    public function scopeEventOrganizableBy(Builder $query, User $user): Builder
    {
        $query->active();

        if ($user->isAdministrator()) {
            return $query;
        }

        return $query->whereHas('activeMemberships', function (Builder $memberships) use ($user): void {
            $memberships
                ->where('user_id', $user->id)
                ->whereIn('role', [
                    OrganizationRole::Owner->value,
                    OrganizationRole::Administrator->value,
                    OrganizationRole::EventManager->value,
                ]);
        });
    }

    /**
     * @param  Builder<Organization>  $query
     * @return Builder<Organization>
     */
    public function scopeAllowingCapability(
        Builder $query,
        OrganizationRestrictionCapability $capability,
    ): Builder {
        return $query->whereDoesntHave('activeRestrictions', function (Builder $restrictions) use ($capability): void {
            $restrictions->where('capability', $capability->value);
        });
    }

    public function isActive(): bool
    {
        return $this->status === OrganizationStatus::Active && $this->archived_at === null;
    }

    public function isVerified(): bool
    {
        return $this->verification_status === OrganizationVerificationStatus::Verified
            && ($this->verification_expires_at === null || $this->verification_expires_at->isFuture());
    }

    public function membershipFor(User $user): ?OrganizationMembership
    {
        if ($this->relationLoaded('memberships')) {
            return $this->memberships
                ->first(fn (OrganizationMembership $membership): bool => $membership->user_id === $user->id
                    && $membership->isActive());
        }

        return $this->memberships()
            ->active()
            ->where('user_id', $user->id)
            ->first();
    }

    public function allows(OrganizationRestrictionCapability $capability): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($this->relationLoaded('restrictions')) {
            return ! $this->restrictions->contains(
                fn (OrganizationRestriction $restriction): bool => $restriction->capability === $capability
                    && $restriction->isActive(),
            );
        }

        return ! $this->restrictions()
            ->active()
            ->where('capability', $capability->value)
            ->exists();
    }
}
