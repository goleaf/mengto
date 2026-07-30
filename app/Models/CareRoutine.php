<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CareRoutineStatus;
use Database\Factories\CareRoutineFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property-read CareJournal|null $careJournal
 * @property int $care_journal_id
 * @property Carbon|null $created_at
 * @property string $created_by_key
 * @property string $created_by_name
 * @property array<array-key, mixed>|null $days
 * @property Carbon|null $ends_on
 * @property int $id
 * @property string|null $instructions
 * @property string $name
 * @property string $period
 * @property string|null $start_time
 * @property Carbon $starts_on
 * @property CareRoutineStatus $status
 * @property-read Collection<int, CareTask> $tasks
 * @property string $timezone
 * @property Carbon|null $updated_at
 * @property int $version
 */
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

    /** @return BelongsTo<\App\Models\CareJournal, $this>*/
    public function careJournal(): BelongsTo
    {
        return $this->belongsTo(CareJournal::class);
    }

    /** @return HasMany<\App\Models\CareTask, $this>*/
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
