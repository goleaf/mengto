<?php

namespace App\Actions;

use App\Enums\CareRoutineStatus;
use App\Models\AuditLog;
use App\Models\CareJournal;
use App\Models\CareRoutine;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;

class CreateCareRoutine
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(CareJournal $journal, array $data): CareRoutine
    {
        return DB::transaction(function () use ($journal, $data): CareRoutine {
            $identity = $this->actor->identity();
            $routine = $journal->routines()->create([
                'name' => $data['name'],
                'period' => $data['period'],
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'] ?? null,
                'days' => array_values($data['days'] ?? []),
                'start_time' => $data['start_time'] ?? null,
                'timezone' => $journal->timezone,
                'status' => CareRoutineStatus::Active,
                'version' => 1,
                'instructions' => $data['instructions'] ?? null,
                'created_by_key' => $identity['key'],
                'created_by_name' => $identity['name'],
            ]);

            AuditLog::query()->create([
                'actor_key' => $identity['key'],
                'actor_role' => 'care-journal-owner',
                'action' => 'care-routine.created',
                'target_type' => CareRoutine::class,
                'target_id' => (string) $routine->id,
                'metadata' => [
                    'care_journal_id' => $journal->id,
                    'period' => $routine->period,
                    'version' => $routine->version,
                ],
            ]);

            return $routine;
        });
    }
}
