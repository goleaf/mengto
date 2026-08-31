<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventAccessibilityStatus;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventInvitationStatus;
use App\Enums\ForumEventPetParticipation;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use App\Enums\ForumGroupStatus;
use App\Services\ForumModerationGuard;
use Carbon\CarbonImmutable;
use Database\Factories\ForumEventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property string|null $accessibility_information
 * @property ForumEventAccessibilityStatus $accessibility_status
 * @property string $animal_welfare_rules
 * @property string|null $attendance_requirements
 * @property int|null $capacity
 * @property CarbonImmutable|null $cancelled_at
 * @property string|null $cancellation_reason_code
 * @property int|null $cancelled_by_user_id
 * @property int $cost_minor
 * @property string $creation_idempotency_key
 * @property string $currency
 * @property string $emergency_contact_plan
 * @property CarbonImmutable $ends_at
 * @property string|null $exact_location
 * @property ForumEventFormat $format
 * @property int|null $forum_group_id
 * @property int $id
 * @property bool $is_system_managed
 * @property string|null $legacy_source_key
 * @property string $locale
 * @property string|null $location_scope
 * @property int $lock_version
 * @property int|null $maximum_animal_age_months
 * @property array<array-key, mixed>|null $metadata
 * @property int|null $minimum_animal_age_months
 * @property string $organizer_key
 * @property string $organizer_name
 * @property int|null $organizer_user_id
 * @property int|null $owner_user_id
 * @property int|null $place_id
 * @property int|null $responsible_organization_id
 * @property int|null $venue_id
 * @property string|null $online_url
 * @property ForumEventPhotoConsent $photo_consent_mode
 * @property ForumEventPetParticipation $pet_participation_mode
 * @property ForumEventRegistrationPolicy $registration_policy
 * @property CarbonImmutable|null $registration_closes_at
 * @property CarbonImmutable|null $registration_opens_at
 * @property string|null $refund_policy
 * @property CarbonImmutable $starts_at
 * @property ForumEventStatus $status
 * @property string $stable_key
 * @property string $summary
 * @property string $timezone
 * @property string $title
 * @property ForumEventType $type
 * @property string|null $vaccination_jurisdiction
 * @property string|null $vaccination_requirements
 * @property ForumEventVisibility $visibility
 * @property bool $waitlist_enabled
 * @property-read ForumGroup|null $group
 * @property-read Collection<int, ForumEventHistory> $history
 * @property-read Collection<int, ForumEventInvitation> $invitations
 * @property-read Collection<int, ForumEventMessage> $messages
 * @property-read User|null $organizer
 * @property-read User|null $owner
 * @property-read Place|null $place
 * @property-read Organization|null $responsibleOrganization
 * @property-read Venue|null $venue
 * @property-read Collection<int, ForumEventOccurrence> $occurrences
 * @property-read Collection<int, ForumEventRegistration> $registrations
 * @property-read Collection<int, ForumEventReview> $reviews
 * @property-read Collection<int, Taxon> $taxa
 * @property-read Collection<int, ForumEventUpdate> $updates
 * @property-read Collection<int, ForumEventVersion> $versions
 * @property-read Collection<int, ForumEventTeamMembership> $teamMemberships
 * @property-read Collection<int, ForumEventTeamMembership> $activeTeamMemberships
 * @property-read Collection<int, ForumEventTrack> $tracks
 * @property-read Collection<int, ForumEventRoom> $rooms
 * @property-read Collection<int, ForumEventSession> $sessions
 */
final class ForumEvent extends Model
{
    /** @use HasFactory<ForumEventFactory> */
    use HasFactory;

    protected $fillable = [
        'organizer_user_id',
        'owner_user_id',
        'responsible_organization_id',
        'place_id',
        'venue_id',
        'organizer_key',
        'organizer_name',
        'forum_group_id',
        'stable_key',
        'creation_idempotency_key',
        'is_system_managed',
        'legacy_source_key',
        'title',
        'summary',
        'type',
        'visibility',
        'format',
        'pet_participation_mode',
        'status',
        'locale',
        'starts_at',
        'ends_at',
        'timezone',
        'capacity',
        'registration_policy',
        'waitlist_enabled',
        'location_scope',
        'exact_location',
        'online_url',
        'attendance_requirements',
        'vaccination_requirements',
        'vaccination_jurisdiction',
        'minimum_animal_age_months',
        'maximum_animal_age_months',
        'accessibility_information',
        'accessibility_status',
        'cost_minor',
        'currency',
        'refund_policy',
        'photo_consent_mode',
        'animal_welfare_rules',
        'emergency_contact_plan',
        'lock_version',
        'current_version_number',
        'registration_opens_at',
        'registration_closes_at',
        'published_at',
        'safety_suspended_at',
        'cancelled_by_user_id',
        'cancelled_at',
        'cancellation_reason_code',
        'archived_at',
        'metadata',
    ];

    protected $hidden = [
        'creation_idempotency_key',
        'exact_location',
        'online_url',
        'emergency_contact_plan',
        'metadata',
    ];

    protected $attributes = [
        'is_system_managed' => false,
        'status' => 'scheduled',
        'visibility' => 'public',
        'pet_participation_mode' => 'optional',
        'accessibility_status' => 'not_assessed',
        'registration_policy' => 'open',
        'waitlist_enabled' => true,
        'cost_minor' => 0,
        'currency' => 'EUR',
        'photo_consent_mode' => 'ask_first',
        'lock_version' => 0,
        'current_version_number' => 1,
    ];

    protected function casts(): array
    {
        return [
            'is_system_managed' => 'boolean',
            'type' => ForumEventType::class,
            'visibility' => ForumEventVisibility::class,
            'format' => ForumEventFormat::class,
            'pet_participation_mode' => ForumEventPetParticipation::class,
            'status' => ForumEventStatus::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'capacity' => 'integer',
            'registration_policy' => ForumEventRegistrationPolicy::class,
            'waitlist_enabled' => 'boolean',
            'exact_location' => 'encrypted',
            'online_url' => 'encrypted',
            'minimum_animal_age_months' => 'integer',
            'maximum_animal_age_months' => 'integer',
            'cost_minor' => 'integer',
            'photo_consent_mode' => ForumEventPhotoConsent::class,
            'accessibility_status' => ForumEventAccessibilityStatus::class,
            'emergency_contact_plan' => 'encrypted',
            'lock_version' => 'integer',
            'current_version_number' => 'integer',
            'registration_opens_at' => 'immutable_datetime',
            'registration_closes_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'safety_suspended_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<User, $this> */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return BelongsTo<Organization, $this> */
    public function responsibleOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'responsible_organization_id');
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return BelongsTo<ForumGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'forum_group_id');
    }

    /** @return BelongsTo<User, $this> */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    /** @return BelongsToMany<Taxon, $this> */
    public function taxa(): BelongsToMany
    {
        return $this->belongsToMany(Taxon::class, 'forum_event_taxon')
            ->withPivot(['is_primary'])
            ->withTimestamps();
    }

    /** @return HasMany<ForumEventRegistration, $this> */
    public function registrations(): HasMany
    {
        return $this->hasMany(ForumEventRegistration::class);
    }

    /** @return HasMany<ForumEventInvitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(ForumEventInvitation::class);
    }

    /** @return HasMany<ForumEventUpdate, $this> */
    public function updates(): HasMany
    {
        return $this->hasMany(ForumEventUpdate::class);
    }

    /** @return HasMany<ForumEventMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(ForumEventMessage::class);
    }

    /** @return HasMany<ForumEventReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(ForumEventReview::class);
    }

    /** @return HasMany<ForumEventHistory, $this> */
    public function history(): HasMany
    {
        return $this->hasMany(ForumEventHistory::class);
    }

    /** @return HasMany<ForumEventOccurrence, $this> */
    public function occurrences(): HasMany
    {
        return $this->hasMany(ForumEventOccurrence::class);
    }

    /** @return HasMany<ForumEventVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(ForumEventVersion::class);
    }

    /** @return HasMany<ForumEventTeamMembership, $this> */
    public function teamMemberships(): HasMany
    {
        return $this->hasMany(ForumEventTeamMembership::class);
    }

    /** @return HasMany<ForumEventTeamMembership, $this> */
    public function activeTeamMemberships(): HasMany
    {
        $memberships = $this->teamMemberships();
        $memberships->getQuery()->active();

        return $memberships;
    }

    /** @return HasMany<ForumEventTrack, $this> */
    public function tracks(): HasMany
    {
        return $this->hasMany(ForumEventTrack::class);
    }

    /** @return HasMany<ForumEventRoom, $this> */
    public function rooms(): HasMany
    {
        return $this->hasMany(ForumEventRoom::class);
    }

    /** @return HasMany<ForumEventSession, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(ForumEventSession::class);
    }

    /** @return HasMany<ForumGroupActivity, $this> */
    public function groupActivities(): HasMany
    {
        return $this->hasMany(ForumGroupActivity::class);
    }

    /** @return HasMany<PlaceAccessAudit, $this> */
    public function placeAccessAudits(): HasMany
    {
        return $this->hasMany(PlaceAccessAudit::class, 'event_id');
    }

    /** @return HasMany<PlaceAccessGrant, $this> */
    public function placeAccessGrants(): HasMany
    {
        return $this->hasMany(PlaceAccessGrant::class, 'event_id');
    }

    /** @return MorphMany<ForumReport, $this> */
    public function subjectReports(): MorphMany
    {
        return $this->morphMany(ForumReport::class, 'subject');
    }

    /**
     * @param  Builder<ForumEvent>  $query
     * @return Builder<ForumEvent>
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        $query
            ->whereNull('archived_at')
            ->whereDoesntHave('moderationActions', static fn (Builder $actions) => $actions
                ->currentlyActive()
                ->whereHas('definition', static fn (Builder $definitions) => $definitions
                    ->whereIn('stable_key', ForumModerationGuard::CONTENT_HIDING_KEYS)));

        if ($user?->isAdministrator() === true) {
            return $query;
        }

        if ($user?->isActive() === true) {
            $query->where(function (Builder $unblocked) use ($user): void {
                $unblocked
                    ->whereNull('organizer_user_id')
                    ->orWhere('organizer_user_id', $user->id)
                    ->orWhere(function (Builder $otherOrganizer) use ($user): void {
                        $otherOrganizer
                            ->whereDoesntHave('organizer.outgoingSocialAccountBlocks', function (Builder $blocks) use ($user): void {
                                $blocks->active()->where('blocked_user_id', $user->id);
                            })
                            ->whereDoesntHave('organizer.incomingSocialAccountBlocks', function (Builder $blocks) use ($user): void {
                                $blocks->active()->where('blocker_user_id', $user->id);
                            });
                    });
            });
        }

        return $query->where(function (Builder $access) use ($user): void {
            if ($user?->isActive() === true) {
                $access->where(function (Builder $managed) use ($user): void {
                    $managed
                        ->where(function (Builder $authority) use ($user): void {
                            $authority
                                ->where('organizer_user_id', $user->id)
                                ->orWhere('owner_user_id', $user->id)
                                ->orWhereHas('activeTeamMemberships', function (Builder $memberships) use ($user): void {
                                    $memberships->where('user_id', $user->id);
                                });
                        })
                        ->where(function (Builder $tenant) use ($user): void {
                            $tenant
                                ->whereNull('responsible_organization_id')
                                ->orWhereHas('responsibleOrganization.activeMemberships', function (Builder $memberships) use ($user): void {
                                    $memberships->where('user_id', $user->id);
                                });
                        });
                });

                $access->orWhere(function (Builder $cancelledHistory) use ($user): void {
                    $cancelledHistory
                        ->where('status', ForumEventStatus::Cancelled->value)
                        ->whereHas('registrations', function (Builder $registrations) use ($user): void {
                            $registrations
                                ->where('user_id', $user->id)
                                ->where('status', ForumEventRegistrationStatus::Cancelled->value)
                                ->where('cancellation_reason_code', 'event-cancelled');
                        });
                });
            }

            $access->orWhere(function (Builder $visible) use ($user): void {
                $visible->whereIn('status', self::discoverableStatusValues())
                    ->where(function (Builder $visibility) use ($user): void {
                        $visibility->where('visibility', ForumEventVisibility::Public->value);

                        if ($user?->isActive() !== true) {
                            return;
                        }

                        $visibility
                            ->orWhere('visibility', ForumEventVisibility::Members->value)
                            ->orWhere(function (Builder $organization) use ($user): void {
                                $organization
                                    ->where('visibility', ForumEventVisibility::Organization->value)
                                    ->whereHas('responsibleOrganization.activeMemberships', function (Builder $memberships) use ($user): void {
                                        $memberships->where('user_id', $user->id);
                                    });
                            })
                            ->orWhere(function (Builder $groups) use ($user): void {
                                $groups
                                    ->where('visibility', ForumEventVisibility::Group->value)
                                    ->whereHas('group', function (Builder $group) use ($user): void {
                                        $group
                                            ->where('status', ForumGroupStatus::Active->value)
                                            ->whereNull('archived_at')
                                            ->where(function (Builder $authority) use ($user): void {
                                                $authority
                                                    ->where('owner_user_id', $user->id)
                                                    ->orWhereHas('memberships', function (Builder $memberships) use ($user): void {
                                                        $memberships
                                                            ->where('user_id', $user->id)
                                                            ->where('state', 'active');
                                                    });
                                            });
                                    });
                            })
                            ->orWhere(function (Builder $private) use ($user): void {
                                $private
                                    ->whereIn('visibility', [
                                        ForumEventVisibility::Private->value,
                                        ForumEventVisibility::Invitation->value,
                                    ])
                                    ->where(function (Builder $tenant) use ($user): void {
                                        $tenant
                                            ->whereNull('responsible_organization_id')
                                            ->orWhereHas('responsibleOrganization.activeMemberships', function (Builder $memberships) use ($user): void {
                                                $memberships->where('user_id', $user->id);
                                            });
                                    })
                                    ->where(function (Builder $participant) use ($user): void {
                                        $participant
                                            ->whereHas('invitations', function (Builder $invitations) use ($user): void {
                                                $invitations
                                                    ->where('invited_user_id', $user->id)
                                                    ->whereIn('status', [
                                                        ForumEventInvitationStatus::Pending->value,
                                                        ForumEventInvitationStatus::Accepted->value,
                                                    ])
                                                    ->where('expires_at', '>', now());
                                            })
                                            ->orWhereHas('registrations', function (Builder $registrations) use ($user): void {
                                                $registrations
                                                    ->where('user_id', $user->id)
                                                    ->whereIn('status', self::participantAccessStatusValues());
                                            });
                                    });
                            });
                    });
            });
        });
    }

    /** @return MorphMany<ForumModerationAction, $this> */
    public function moderationActions(): MorphMany
    {
        return $this->morphMany(ForumModerationAction::class, 'target');
    }

    public function isOrganizer(User $user): bool
    {
        return $this->organizer_user_id === $user->id
            || hash_equals($this->organizer_key, $user->actor_key);
    }

    public function isOwner(User $user): bool
    {
        return ($this->getAttributes()['owner_user_id'] ?? null) === $user->id;
    }

    public function hasEnded(): bool
    {
        return $this->ends_at->isPast()
            || $this->status === ForumEventStatus::Completed;
    }

    public function registrationWindowIsOpen(?CarbonImmutable $at = null): bool
    {
        $at ??= CarbonImmutable::now();

        return ($this->registration_opens_at === null || $this->registration_opens_at->lessThanOrEqualTo($at))
            && ($this->registration_closes_at === null || $this->registration_closes_at->isAfter($at));
    }

    public function registrationFor(User $user): ?ForumEventRegistration
    {
        if ($this->relationLoaded('registrations')) {
            return $this->registrations
                ->where('user_id', $user->id)
                ->sortByDesc('id')
                ->first();
        }

        return $this->registrations()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->first();
    }

    public function canDiscloseAccessTo(User $user): bool
    {
        if ($user->isAdministrator() || $this->isOrganizer($user)) {
            return true;
        }

        if ($this->hasEnded() || $this->status === ForumEventStatus::Cancelled) {
            return false;
        }

        return in_array(
            $this->registrationFor($user)?->status,
            [
                ForumEventRegistrationStatus::Confirmed,
                ForumEventRegistrationStatus::CheckedIn,
                ForumEventRegistrationStatus::PartiallyCheckedIn,
            ],
            true,
        );
    }

    /** @return list<string> */
    public static function participantAccessStatusValues(): array
    {
        return array_map(
            static fn (ForumEventRegistrationStatus $status): string => $status->value,
            [
                ForumEventRegistrationStatus::Confirmed,
                ForumEventRegistrationStatus::CheckedIn,
                ForumEventRegistrationStatus::PartiallyCheckedIn,
                ForumEventRegistrationStatus::Attended,
                ForumEventRegistrationStatus::Completed,
            ],
        );
    }

    /** @return list<string> */
    private static function discoverableStatusValues(): array
    {
        return collect(ForumEventStatus::cases())
            ->filter(static fn (ForumEventStatus $status): bool => $status->isDiscoverable())
            ->map(static fn (ForumEventStatus $status): string => $status->value)
            ->values()
            ->all();
    }
}
