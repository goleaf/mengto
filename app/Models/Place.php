<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationRole;
use App\Enums\PlaceAccessibilityStatus;
use App\Enums\PlaceAccessPurpose;
use App\Enums\PlaceStatus;
use App\Enums\PlaceType;
use App\Enums\PlaceVerificationStatus;
use App\Enums\PlaceVisibility;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property PlaceAccessibilityStatus $accessibility_status
 * @property array<int|string, mixed>|null $accessibility_facts
 * @property CarbonImmutable|null $archived_at
 * @property string|null $catalog_category
 * @property string $creation_idempotency_key
 * @property string|null $exact_address
 * @property string|null $exact_latitude
 * @property string|null $exact_longitude
 * @property int $id
 * @property CarbonImmutable|null $information_expires_at
 * @property string $locale
 * @property int $lock_version
 * @property string $name
 * @property int|null $organization_id
 * @property int|null $owner_user_id
 * @property string|null $private_instructions
 * @property string|null $parking_information
 * @property string|null $public_address
 * @property string|null $public_email
 * @property numeric-string|null $public_latitude
 * @property numeric-string|null $public_longitude
 * @property string|null $public_phone
 * @property string $public_region
 * @property string|null $public_website
 * @property string $slug
 * @property string $stable_key
 * @property PlaceStatus $status
 * @property string|null $summary
 * @property string|null $pet_rules
 * @property list<string>|null $species_rules
 * @property PlaceType $type
 * @property Carbon $updated_at
 * @property PlaceVerificationStatus $verification_status
 * @property PlaceVisibility $visibility
 * @property-read Collection<int, PlaceAccessAudit> $accessAudits
 * @property-read Collection<int, PlaceAccessGrant> $accessGrants
 * @property-read Collection<int, ForumEvent> $events
 * @property-read Collection<int, PlaceLocationVersion> $locationVersions
 * @property-read Collection<int, PlaceQuestion> $questions
 * @property-read Organization|null $organization
 * @property-read User|null $owner
 * @property-read Venue|null $venue
 */
final class Place extends Model
{
    /** @use HasFactory<PlaceFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'organization_id',
        'created_by_user_id',
        'last_edited_by_user_id',
        'stable_key',
        'slug',
        'creation_idempotency_key',
        'name',
        'summary',
        'type',
        'catalog_category',
        'visibility',
        'status',
        'locale',
        'public_region',
        'public_address',
        'public_phone',
        'public_website',
        'public_email',
        'public_latitude',
        'public_longitude',
        'exact_address',
        'exact_latitude',
        'exact_longitude',
        'private_instructions',
        'is_indoor',
        'verification_status',
        'verification_source',
        'verified_at',
        'information_expires_at',
        'accessibility_status',
        'accessibility_facts',
        'transport_information',
        'parking_information',
        'pet_rules',
        'species_rules',
        'lock_version',
        'metadata',
        'archived_at',
    ];

    protected $hidden = [
        'creation_idempotency_key',
        'exact_address',
        'exact_latitude',
        'exact_longitude',
        'private_instructions',
        'metadata',
    ];

    protected $attributes = [
        'visibility' => 'public',
        'status' => 'active',
        'locale' => 'en',
        'verification_status' => 'not_assessed',
        'accessibility_status' => 'not_assessed',
        'is_indoor' => false,
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'type' => PlaceType::class,
            'visibility' => PlaceVisibility::class,
            'status' => PlaceStatus::class,
            'public_latitude' => 'decimal:6',
            'public_longitude' => 'decimal:6',
            'exact_address' => 'encrypted',
            'exact_latitude' => 'encrypted',
            'exact_longitude' => 'encrypted',
            'private_instructions' => 'encrypted',
            'is_indoor' => 'boolean',
            'verification_status' => PlaceVerificationStatus::class,
            'verified_at' => 'immutable_datetime',
            'information_expires_at' => 'immutable_datetime',
            'accessibility_status' => PlaceAccessibilityStatus::class,
            'accessibility_facts' => 'array',
            'species_rules' => 'array',
            'lock_version' => 'integer',
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

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return HasOne<Venue, $this> */
    public function venue(): HasOne
    {
        return $this->hasOne(Venue::class);
    }

    /** @return HasMany<PlaceAccessGrant, $this> */
    public function accessGrants(): HasMany
    {
        return $this->hasMany(PlaceAccessGrant::class);
    }

    /** @return HasMany<PlaceAccessAudit, $this> */
    public function accessAudits(): HasMany
    {
        return $this->hasMany(PlaceAccessAudit::class);
    }

    /** @return HasMany<PlaceLocationVersion, $this> */
    public function locationVersions(): HasMany
    {
        return $this->hasMany(PlaceLocationVersion::class);
    }

    /** @return HasMany<PlaceQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(PlaceQuestion::class);
    }

    /** @return HasMany<ForumEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ForumEvent::class);
    }

    /** @param Builder<Place> $query @return Builder<Place> */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', PlaceStatus::Active->value)
            ->whereNull('archived_at');
    }

    /** @param Builder<Place> $query @return Builder<Place> */
    public function scopePubliclyDiscoverable(Builder $query): Builder
    {
        return $query
            ->active()
            ->where('visibility', PlaceVisibility::Public->value);
    }

    /** @param Builder<Place> $query @return Builder<Place> */
    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        $query->active();

        if ($user->isAdministrator()) {
            return $query;
        }

        return $query->where(function (Builder $visibility) use ($user): void {
            $visibility
                ->where('visibility', PlaceVisibility::Public->value)
                ->orWhere('owner_user_id', $user->id)
                ->orWhere(function (Builder $organizationVisible) use ($user): void {
                    $organizationVisible
                        ->where('visibility', PlaceVisibility::Organization->value)
                        ->whereHas('organization.activeMemberships', function (Builder $memberships) use ($user): void {
                            $memberships->where('user_id', $user->id);
                        });
                })
                ->orWhereHas('organization.activeMemberships', function (Builder $memberships) use ($user): void {
                    $memberships
                        ->where('user_id', $user->id)
                        ->whereIn('role', OrganizationRole::placeManagerValues());
                })
                ->orWhereHas('accessGrants', function (Builder $grants) use ($user): void {
                    PlaceAccessGrant::constrainActiveAt($grants, now())
                        ->where('user_id', $user->id)
                        ->where('may_view_exact_location', true);
                });
        });
    }

    /** @param Builder<Place> $query @return Builder<Place> */
    public function scopeUsableForEventsBy(Builder $query, User $user): Builder
    {
        $query->active();

        if ($user->isAdministrator()) {
            return $query;
        }

        return $query->where(function (Builder $usable) use ($user): void {
            $usable
                ->where('visibility', PlaceVisibility::Public->value)
                ->orWhere('owner_user_id', $user->id)
                ->orWhereHas('organization.activeMemberships', function (Builder $memberships) use ($user): void {
                    $memberships
                        ->where('user_id', $user->id)
                        ->whereIn('role', OrganizationRole::placeManagerValues());
                })
                ->orWhereHas('accessGrants', function (Builder $grants) use ($user): void {
                    PlaceAccessGrant::constrainActiveAt($grants, now())
                        ->where('user_id', $user->id)
                        ->where('purpose', PlaceAccessPurpose::EventOperations->value)
                        ->where('may_view_exact_location', true);
                });
        });
    }

    public function isManagedBy(User $user): bool
    {
        if ($user->isAdministrator() || $this->owner_user_id === $user->id) {
            return true;
        }

        $membership = $this->organization?->membershipFor($user);

        return $membership?->role->canManagePlaces() === true;
    }

    public function isVisibleToOrganizationMember(User $user): bool
    {
        return $this->visibility === PlaceVisibility::Organization
            && $this->organization?->membershipFor($user) !== null;
    }

    public function activeExactGrantFor(User $user): ?PlaceAccessGrant
    {
        return $this->activeExactGrantsFor($user)
            ->orderByDesc('valid_until')
            ->first();
    }

    public function hasActiveExactGrantFor(User $user, PlaceAccessPurpose $purpose): bool
    {
        return $this->activeExactGrantsFor($user)
            ->where('purpose', $purpose->value)
            ->exists();
    }

    /** @return HasMany<PlaceAccessGrant, $this> */
    public function activeExactGrantsFor(User $user): HasMany
    {
        return $this->accessGrants()
            ->active()
            ->where('user_id', $user->id)
            ->where('may_view_exact_location', true);
    }
}
