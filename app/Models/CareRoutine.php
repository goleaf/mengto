<?php

namespace App\Models;

use App\Enums\CareRoutineStatus;
use Database\Factories\CareRoutineFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareRoutine extends Model
{
    /** @use HasFactory<CareRoutineFactory> */
    use HasFactory;

    protected $fillable = [
        'care_journal_id', 'name', 'period', 'starts_on', 'ends_on', 'days',
        'start_time', 'timezone', 'status', 'version', 'instructions',
        'created_by_key', 'created_by_name',
    ];

    protected $hidden = ['instructions'];

    protected $attributes = [
        'period' => 'daily',
        'status' => 'active',
        'version' => 1,
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'days' => 'array',
            'status' => CareRoutineStatus::class,
            'instructions' => 'encrypted',
        ];
    }

    public function careJournal(): BelongsTo
    {
        return $this->belongsTo(CareJournal::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(CareTask::class);
    }

    public function scopeActiveForJournal(Builder $query, int $journalId): Builder
    {
        return $query
            ->select([
                'id', 'care_journal_id', 'name', 'period', 'starts_on',
                'ends_on', 'days', 'start_time', 'timezone', 'status',
                'version', 'instructions', 'created_by_name',
            ])
            ->where('care_journal_id', $journalId)
            ->where('status', CareRoutineStatus::Active->value);
    }
}
