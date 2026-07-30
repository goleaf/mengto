<?php

namespace App\Actions;

use App\Enums\CareEntryType;
use App\Enums\CareTaskPriority;
use App\Enums\CareTaskStatus;
use App\Models\AuditLog;
use App\Models\CareJournal;
use App\Models\CareRoutine;
use App\Models\CareTask;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateCareTask
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(CareJournal $journal, array $data): CareTask
    {
        return DB::transaction(function () use ($journal, $data): CareTask {
            $routineId = $data['care_routine_id'] ?? null;

            if ($routineId !== null) {
                $belongs = CareRoutine::query()
                    ->whereKey($routineId)
                    ->where('care_journal_id', $journal->id)
                    ->exists();

                if (! $belongs) {
                    throw ValidationException::withMessages([
                        'care_routine_id' => __('messages.this_routine_does_not_belong_to_the_selected_journal_ed6a8c6fd7'),
                    ]);
                }
            }

            $identity = $this->actor->identity();
            $task = $journal->tasks()->create([
                'care_routine_id' => $routineId,
                'title' => $data['title'],
                'type' => CareEntryType::from($data['type']),
                'assignee_key' => ($data['assignee_key'] ?? null) ?: $identity['key'],
                'assignee_name' => ($data['assignee_name'] ?? null) ?: $identity['name'],
                'due_at' => $data['due_at'],
                'timezone' => $journal->timezone,
                'repeat_rule' => $data['repeat_rule'] ?? null,
                'priority' => CareTaskPriority::from($data['priority']),
                'status' => CareTaskStatus::Planned,
                'instructions' => $data['instructions'] ?? null,
                'requires_individual_confirmation' => (bool) (
                    $data['requires_individual_confirmation'] ?? false
                ),
                'created_by_key' => $identity['key'],
                'created_by_name' => $identity['name'],
            ]);

            AuditLog::query()->create([
                'actor_key' => $identity['key'],
                'actor_role' => 'care-journal-owner',
                'action' => 'care-task.created',
                'target_type' => CareTask::class,
                'target_id' => (string) $task->id,
                'metadata' => [
                    'care_journal_id' => $journal->id,
                    'type' => $task->type->value,
                    'priority' => $task->priority->value,
                ],
            ]);

            return $task;
        });
    }
}
