<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventSessionReservationPolicy;
use App\Enums\ForumEventSessionStatus;
use App\Enums\ForumEventSessionType;
use Carbon\CarbonImmutable;
use Database\Factories\ForumEventSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int|null $capacity
 * @property array<string, mixed>|null $conflict_snapshot
 * @property string|null $conflict_override_reason
 * @property int|null $created_by_user_id
 * @property CarbonImmutable $ends_at
 * @property int $forum_event_id
 * @property int $forum_event_occurrence_id
 * @property int|null $forum_event_room_id
 * @property int|null $forum_event_track_id
 * @property int $id
 * @property string $idempotency_key
 * @property bool $is_required
 * @property int $lock_version
 * @property int $position
 * @property ForumEventSessionReservationPolicy $reservation_policy
 * @property string|null $summary
 * @property CarbonImmutable $starts_at
 * @property ForumEventSessionStatus $status
 * @property string $stable_key
 * @property string $timezone
 * @property string $title
 * @property ForumEventSessionType $type
 * @property int|null $updated_by_user_id
 * @property-read ForumEvent $event
 * @property-read ForumEventOccurrence $occurrence
 * @property-read ForumEventRoom|null $room
 * @property-read ForumEventTrack|null $track
 */
final class ForumEventSession extends Model
{
    /** @use HasFactory<ForumEventSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_event_id',
        'forum_event_occurrence_id',
        'forum_event_track_id',
        'forum_event_room_id',
        'created_by_user_id',
        'updated_by_user_id',
        'stable_key',
        'idempotency_key',
        'title',
        'summary',
        'type',
        'status',
        'starts_at',
        'ends_at',
        'timezone',
        'capacity',
        'reservation_policy',
        'is_required',
        'position',
        'conflict_override_reason',
        'conflict_snapshot',
        'lock_version',
    ];

    protected $hidden = [
        'idempotency_key',
        'conflict_override_reason',
        'conflict_snapshot',
    ];

    protected $attributes = [
        'type' => 'session',
        'status' => 'scheduled',
        'reservation_policy' => 'optional',
        'is_required' => false,
        'position' => 0,
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'type' => ForumEventSessionType::class,
            'status' => ForumEventSessionStatus::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'capacity' => 'integer',
            'reservation_policy' => ForumEventSessionReservationPolicy::class,
            'is_required' => 'boolean',
            'position' => 'integer',
            'conflict_override_reason' => 'encrypted',
            'conflict_snapshot' => 'encrypted:array',
            'lock_version' => 'integer',
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

    /** @return BelongsTo<ForumEventTrack, $this> */
    public function track(): BelongsTo
    {
        return $this->belongsTo(ForumEventTrack::class, 'forum_event_track_id');
    }

    /** @return BelongsTo<ForumEventRoom, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(ForumEventRoom::class, 'forum_event_room_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /** @return HasMany<ForumEventSessionStaff, $this> */
    public function staffAssignments(): HasMany
    {
        return $this->hasMany(ForumEventSessionStaff::class);
    }
}
