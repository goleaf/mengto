<?php

namespace App\Actions;

use App\Models\CareEntry;
use App\Models\CareJournal;
use App\Models\CareTask;

class CompleteCareTask
{
    public function __construct(private readonly CreateCareEntry $entries) {}

    /** @param array<string, mixed> $data */
    public function handle(
        CareJournal $journal,
        CareTask $task,
        array $data,
    ): CareEntry {
        return $this->entries->handle($journal, [
            'idempotency_key' => $data['idempotency_key'],
            'care_task_id' => $task->id,
            'entry_type' => $task->type->value,
            'title' => $task->title,
            'started_at' => now()->format('Y-m-d H:i:s'),
            'status' => $data['status'],
            'source_type' => 'owner',
            'source_name' => '',
            'notes' => $data['completion_note'] ?? null,
            'confirm_duplicate' => $data['confirm_duplicate'] ?? false,
        ]);
    }
}
