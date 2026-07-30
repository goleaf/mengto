<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CareEntryType;
use App\Enums\CareTaskPriority;
use App\Enums\CareTaskStatus;
use Database\Factories\CareTaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string|null $assignee_key
 * @property string|null $assignee_name
 * @property-read CareJournal|null $careJournal
 * @property int $care_journal_id
 * @property int|null $care_routine_id
 * @property Carbon|null $completed_at
 * @property string|null $completed_by_key
 * @property string|null $completed_by_name
 * @property-read CareEntry|null $completionEntry
 * @property string|null $completion_note
 * @property Carbon|null $created_at
 * @property string $created_by_key
 * @property string $created_by_name
 * @property Carbon $due_at
 * @property int $id
 * @property string|null $instructions
 * @property CareTaskPriority $priority
 * @property string|null $repeat_rule
 * @property bool $requires_individual_confirmation
 * @property-read CareRoutine|null $routine
 * @property CareTaskStatus $status
 * @property string $timezone
 * @property string $title
 * @property CareEntryType $type
 * @property Carbon|null $updated_at
 */
class CareTask extends Model
{
    /** @use HasFactory<CareTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'care_journal_id', 'care_routine_id', 'title', 'type', 'assignee_key',
        'assignee_name', 'due_at', 'timezone', 'repeat_rule', 'priority',
        'status', 'instructions', 'requires_individual_confirmation',
        'completed_at', 'completed_by_key', 'completed_by_name',
        'completion_note', 'created_by_key', 'created_by_name',
    ];

    protected $hidden = ['instructions', 'completion_note'];

    protected $attributes = [
        'priority' => 'normal',
        'status' => 'planned',
        'requires_individual_confirmation' => false,
    ];

    protected function casts(): array
    {
        return [
            'type' => CareEntryType::class,
            'due_at' => 'datetime',
            'priority' => CareTaskPriority::class,
            'status' => CareTaskStatus::class,
            'instructions' => 'encrypted',
            'requires_individual_confirmation' => 'boolean',
            'completed_at' => 'datetime',
            'completion_note' => 'encrypted',
        ];
    }

    /** @return BelongsTo<\App\Models\CareJournal, $this>*/
    public function careJournal(): BelongsTo
    {
        return $this->belongsTo(CareJournal::class);
    }

    /** @return BelongsTo<\App\Models\CareRoutine, $this>*/
    public function routine(): BelongsTo
    {
        return $this->belongsTo(CareRoutine::class, 'care_routine_id');
    }

    /** @return HasOne<\App\Models\CareEntry, $this>*/
    public function completionEntry(): HasOne
    {
        return $this->hasOne(CareEntry::class);
    }

    public function scopeOpenForJournal(Builder $query, int $journalId): Builder
    {
        return $query
            ->select([
                'id', 'care_journal_id', 'care_routine_id', 'title', 'type',
                'assignee_key', 'assignee_name', 'due_at', 'timezone',
                'repeat_rule', 'priority', 'status', 'instructions',
                'requires_individual_confirmation', 'created_by_name',
            ])
            ->where('care_journal_id', $journalId)
            ->whereIn('status', [
                CareTaskStatus::Planned->value,
                CareTaskStatus::DueSoon->value,
                CareTaskStatus::Postponed->value,
                CareTaskStatus::NeedsHelp->value,
            ]);
    }
}
