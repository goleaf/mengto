<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventFormat;
use App\Enums\ForumEventStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ForumEventOccurrenceFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int|null $capacity
 * @property CarbonImmutable|null $cancelled_at
 * @property string|null $cancellation_reason_code
 * @property CarbonImmutable $ends_at
 * @property string|null $exact_location
 * @property ForumEventFormat $format
 * @property int $forum_event_id
 * @property int|null $forum_event_series_id
 * @property int $id
 * @property bool $is_override
 * @property string|null $location_scope
 * @property array<string, mixed>|null $metadata
 * @property string|null $online_url
 * @property CarbonImmutable $starts_at
 * @property ForumEventStatus $status
 * @property string $stable_key
 * @property string $timezone
 * @property-read Collection<int, ForumEventSession> $sessions
 */
final class ForumEventOccurrence extends Model
{
    /** @use HasFactory<ForumEventOccurrenceFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_event_id',
        'forum_event_series_id',
        'stable_key',
        'status',
        'starts_at',
        'ends_at',
        'timezone',
        'format',
        'capacity',
        'location_scope',
        'exact_location',
        'online_url',
        'is_override',
        'cancelled_at',
        'cancellation_reason_code',
        'metadata',
    ];

    protected $hidden = ['exact_location', 'online_url', 'metadata'];

    protected $attributes = [
        'status' => 'scheduled',
        'is_override' => false,
    ];

    protected function casts(): array
    {
        return [
            'status' => ForumEventStatus::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'format' => ForumEventFormat::class,
            'capacity' => 'integer',
            'exact_location' => 'encrypted',
            'online_url' => 'encrypted',
            'is_override' => 'boolean',
            'cancelled_at' => 'immutable_datetime',
            'metadata' => 'encrypted:array',
        ];
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return BelongsTo<ForumEventSeries, $this> */
    public function series(): BelongsTo
    {
        return $this->belongsTo(ForumEventSeries::class, 'forum_event_series_id');
    }

    /** @return HasMany<ForumEventRegistration, $this> */
    public function registrations(): HasMany
    {
        return $this->hasMany(ForumEventRegistration::class);
    }

    /** @return HasMany<ForumEventSession, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(ForumEventSession::class);
    }
}
