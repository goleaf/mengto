<?php

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

    public function careJournal(): BelongsTo
    {
        return $this->belongsTo(CareJournal::class);
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(CareRoutine::class, 'care_routine_id');
    }

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
