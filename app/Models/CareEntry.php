<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CareEntryStatus;
use App\Enums\CareEntryType;
use App\Enums\CareSourceType;
use App\Enums\CareSyncStatus;
use Database\Factories\CareEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string|null $appetite
 * @property string $author_key
 * @property string $author_name
 * @property Carbon|null $cancelled_at
 * @property string|null $cancelled_by_key
 * @property-read CareJournal|null $careJournal
 * @property int $care_journal_id
 * @property int|null $care_task_id
 * @property array<array-key, mixed>|null $context
 * @property Carbon|null $created_at
 * @property int|null $distance_meters
 * @property int|null $duration_minutes
 * @property Carbon|null $ended_at
 * @property int $id
 * @property string $idempotency_key
 * @property string|null $intensity
 * @property bool $is_unusual
 * @property array<array-key, mixed>|null $measurements
 * @property-read Collection<int, CareMedia> $media
 * @property string|null $notes
 * @property string $privacy
 * @property string|null $quantity_unit
 * @property numeric-string|null $quantity_value
 * @property string $source_name
 * @property Carbon|null $source_recorded_at
 * @property string|null $source_timezone
 * @property CareSourceType $source_type
 * @property Carbon $started_at
 * @property CareEntryStatus $status
 * @property string|null $subtype
 * @property-read CareTask|null $task
 * @property string $timezone
 * @property string $title
 * @property CareEntryType $type
 * @property CareSyncStatus $sync_status
 * @property Carbon|null $synchronized_at
 * @property Carbon|null $updated_at
 * @property string $verification_status
 */
class CareEntry extends Model
{
    /** @use HasFactory<CareEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'care_journal_id', 'care_task_id', 'idempotency_key', 'type',
        'subtype', 'started_at', 'ended_at', 'timezone', 'status',
        'source_type', 'source_name', 'verification_status', 'author_key',
        'author_name', 'title', 'notes', 'measurements', 'context',
        'quantity_value', 'quantity_unit', 'duration_minutes',
        'distance_meters', 'appetite', 'intensity', 'is_unusual', 'privacy',
        'cancelled_at', 'cancelled_by_key', 'source_recorded_at',
        'source_timezone', 'sync_status', 'synchronized_at',
    ];

    protected $hidden = ['notes', 'measurements', 'context'];

    protected $attributes = [
        'status' => 'completed',
        'source_type' => 'owner',
        'verification_status' => 'person-reported',
        'is_unusual' => false,
        'privacy' => 'private',
        'sync_status' => 'direct',
    ];

    protected function casts(): array
    {
        return [
            'type' => CareEntryType::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'status' => CareEntryStatus::class,
            'source_type' => CareSourceType::class,
            'source_recorded_at' => 'immutable_datetime',
            'sync_status' => CareSyncStatus::class,
            'synchronized_at' => 'immutable_datetime',
            'notes' => 'encrypted',
            'measurements' => 'encrypted:array',
            'context' => 'encrypted:array',
            'quantity_value' => 'decimal:3',
            'is_unusual' => 'boolean',
            'cancelled_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\CareJournal, $this>*/
    public function careJournal(): BelongsTo
    {
        return $this->belongsTo(CareJournal::class);
    }

    /** @return BelongsTo<\App\Models\CareTask, $this>*/
    public function task(): BelongsTo
    {
        return $this->belongsTo(CareTask::class, 'care_task_id');
    }

    /** @return HasMany<\App\Models\CareMedia, $this>*/
    public function media(): HasMany
    {
        return $this->hasMany(CareMedia::class);
    }

    /** @return HasMany<DeviceEvent, $this> */
    public function deviceEvents(): HasMany
    {
        return $this->hasMany(DeviceEvent::class);
    }

    /** @return HasMany<DeviceReading, $this> */
    public function deviceReadings(): HasMany
    {
        return $this->hasMany(DeviceReading::class);
    }

    public function scopeForTimeline(Builder $query): Builder
    {
        return $query->select([
            'id', 'care_journal_id', 'care_task_id', 'type', 'subtype',
            'started_at', 'ended_at', 'timezone', 'status', 'source_type',
            'source_name', 'verification_status', 'author_name', 'title',
            'notes', 'measurements', 'context', 'quantity_value',
            'quantity_unit', 'duration_minutes', 'distance_meters',
            'appetite', 'intensity', 'is_unusual', 'privacy', 'created_at',
            'source_recorded_at', 'source_timezone', 'sync_status',
            'synchronized_at',
        ]);
    }

    public function scopeForPeriod(Builder $query, int $journalId, mixed $from, mixed $to): Builder
    {
        return $this->scopeForTimeline($query)
            ->where('care_journal_id', $journalId)
            ->whereBetween('started_at', [$from, $to]);
    }
}
