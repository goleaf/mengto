<?php

namespace App\Models;

use App\Enums\CareEntryStatus;
use App\Enums\CareEntryType;
use App\Enums\CareSourceType;
use Database\Factories\CareEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'cancelled_at', 'cancelled_by_key',
    ];

    protected $hidden = ['notes', 'measurements', 'context'];

    protected $attributes = [
        'status' => 'completed',
        'source_type' => 'owner',
        'verification_status' => 'person-reported',
        'is_unusual' => false,
        'privacy' => 'private',
    ];

    protected function casts(): array
    {
        return [
            'type' => CareEntryType::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'status' => CareEntryStatus::class,
            'source_type' => CareSourceType::class,
            'notes' => 'encrypted',
            'measurements' => 'encrypted:array',
            'context' => 'encrypted:array',
            'quantity_value' => 'decimal:3',
            'is_unusual' => 'boolean',
            'cancelled_at' => 'datetime',
        ];
    }

    public function careJournal(): BelongsTo
    {
        return $this->belongsTo(CareJournal::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(CareTask::class, 'care_task_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(CareMedia::class);
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
        ]);
    }

    public function scopeForPeriod(Builder $query, int $journalId, mixed $from, mixed $to): Builder
    {
        return $query
            ->forTimeline()
            ->where('care_journal_id', $journalId)
            ->whereBetween('started_at', [$from, $to]);
    }
}
