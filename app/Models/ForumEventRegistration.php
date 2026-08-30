<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ForumEventRegistrationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property ForumEventFormat $attendance_format
 * @property array<string, mixed>|null $accepted_snapshot
 * @property string|null $accepted_snapshot_checksum
 * @property CarbonImmutable|null $cancelled_at
 * @property string|null $cancellation_reason_code
 * @property string|null $check_in_method
 * @property CarbonImmutable|null $checked_in_at
 * @property CarbonImmutable|null $checked_out_at
 * @property CarbonImmutable|null $confirmed_at
 * @property int $forum_event_id
 * @property int|null $forum_event_occurrence_id
 * @property int|null $forum_event_version_id
 * @property int $guest_count
 * @property int $id
 * @property string $idempotency_key
 * @property string|null $active_scope_key
 * @property int $lock_version
 * @property string|null $locale
 * @property int|null $pet_profile_id
 * @property ForumEventPhotoConsent $photo_consent
 * @property string|null $requirements_note
 * @property bool $requirements_accepted
 * @property string $stable_key
 * @property ForumEventRegistrationStatus $status
 * @property CarbonImmutable|null $status_changed_at
 * @property CarbonImmutable|null $submitted_at
 * @property string|null $timezone
 * @property int $user_id
 * @property int|null $waitlist_position
 * @property-read ForumEvent $event
 * @property-read ForumEventOccurrence|null $occurrence
 * @property-read ForumEventVersion|null $version
 * @property-read Collection<int, PetProfile> $pets
 * @property-read PetProfile|null $petProfile
 * @property-read User $user
 * @property-read Collection<int, ForumEventParticipationTransition> $transitions
 */
final class ForumEventRegistration extends Model
{
    /** @use HasFactory<ForumEventRegistrationFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_event_id',
        'forum_event_occurrence_id',
        'forum_event_version_id',
        'user_id',
        'pet_profile_id',
        'stable_key',
        'idempotency_key',
        'active_scope_key',
        'participation_role',
        'current_snapshot_id',
        'current_eligibility_decision_set_id',
        'eligibility_stale_at',
        'acceptance_stale_at',
        'status_changed_at',
        'status',
        'attendance_format',
        'guest_count',
        'requirements_note',
        'photo_consent',
        'requirements_accepted',
        'waitlist_position',
        'check_in_method',
        'checked_in_at',
        'cancelled_at',
        'cancellation_reason_code',
        'lock_version',
        'accepted_snapshot',
        'accepted_snapshot_checksum',
        'locale',
        'timezone',
        'submitted_at',
        'confirmed_at',
        'checked_out_at',
    ];

    protected $hidden = [
        'idempotency_key',
        'requirements_note',
        'accepted_snapshot',
    ];

    protected $attributes = [
        'guest_count' => 0,
        'photo_consent' => 'ask_first',
        'requirements_accepted' => false,
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'status' => ForumEventRegistrationStatus::class,
            'attendance_format' => ForumEventFormat::class,
            'guest_count' => 'integer',
            'requirements_note' => 'encrypted',
            'photo_consent' => ForumEventPhotoConsent::class,
            'requirements_accepted' => 'boolean',
            'waitlist_position' => 'integer',
            'checked_in_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'eligibility_stale_at' => 'immutable_datetime',
            'acceptance_stale_at' => 'immutable_datetime',
            'status_changed_at' => 'immutable_datetime',
            'lock_version' => 'integer',
            'accepted_snapshot' => 'encrypted:array',
            'submitted_at' => 'immutable_datetime',
            'confirmed_at' => 'immutable_datetime',
            'checked_out_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return BelongsTo<ForumEventOccurrence, $this> */
    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(ForumEventOccurrence::class, 'forum_event_occurrence_id');
    }

    /** @return BelongsTo<ForumEventVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(ForumEventVersion::class, 'forum_event_version_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function petProfile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class)->withTrashed();
    }

    /** @return BelongsToMany<PetProfile, $this, ForumEventRegistrationPet, 'pivot'> */
    public function pets(): BelongsToMany
    {
        return $this->belongsToMany(
            PetProfile::class,
            'forum_event_registration_pets',
        )->using(ForumEventRegistrationPet::class)->withPivot([
            'eligibility_status',
            'verification_source',
            'conditions',
            'checked_in_at',
            'checked_out_at',
        ])->withTimestamps();
    }

    /** @return HasMany<ForumEventRegistrationPet, $this> */
    public function registrationPets(): HasMany
    {
        return $this->hasMany(ForumEventRegistrationPet::class);
    }

    /** @return HasMany<ForumEventParticipationTransition, $this> */
    public function transitions(): HasMany
    {
        return $this->hasMany(ForumEventParticipationTransition::class);
    }
}
