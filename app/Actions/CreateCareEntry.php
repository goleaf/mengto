<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CareEntryStatus;
use App\Enums\CareEntryType;
use App\Enums\CareSourceType;
use App\Enums\CareSyncStatus;
use App\Enums\CareTaskStatus;
use App\Models\AuditLog;
use App\Models\CareAccessGrant;
use App\Models\CareEntry;
use App\Models\CareJournal;
use App\Models\CareMedia;
use App\Models\CareTask;
use App\Services\ForumActor;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateCareEntry
{
    public function __construct(private readonly ForumActor $actor) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array{key: string, name: string, role: string}|null  $contributor
     */
    public function handle(
        CareJournal $journal,
        array $data,
        ?array $contributor = null,
        ?CareAccessGrant $accessGrant = null,
    ): CareEntry {
        $storedPath = null;

        try {
            return DB::transaction(function () use (
                $journal,
                $data,
                $contributor,
                $accessGrant,
                &$storedPath,
            ): CareEntry {
                $existing = CareEntry::query()
                    ->select(['id', 'care_journal_id', 'idempotency_key'])
                    ->where('idempotency_key', $data['idempotency_key'])
                    ->first();

                if ($existing !== null) {
                    if ($existing->care_journal_id !== $journal->id) {
                        throw ValidationException::withMessages([
                            'idempotency_key' => __('messages.this_submission_key_is_already_in_use'),
                        ]);
                    }

                    return CareEntry::query()->findOrFail($existing->id);
                }

                $lockedJournal = CareJournal::query()
                    ->select([
                        'id', 'owner_key', 'pet_name', 'timezone', 'status',
                        'last_feeding_at', 'last_water_at', 'last_walk_at',
                        'last_toilet_at', 'lock_version',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($journal->id);

                if ($lockedJournal->status !== 'active') {
                    throw ValidationException::withMessages([
                        'entry_type' => __('messages.this_care_journal_is_not_active'),
                    ]);
                }

                $type = CareEntryType::from($data['entry_type']);
                $status = CareEntryStatus::from($data['status']);
                $source = CareSourceType::from(
                    $contributor === null ? $data['source_type'] : $contributor['role'],
                );
                $sourceTimezone = (string) (($data['source_timezone'] ?? null) ?: $lockedJournal->timezone);
                $startedAt = CarbonImmutable::parse(
                    (string) $data['started_at'],
                    $sourceTimezone,
                )->utc();
                $endedAt = ($data['ended_at'] ?? null) === null
                    ? null
                    : CarbonImmutable::parse(
                        (string) $data['ended_at'],
                        $sourceTimezone,
                    )->utc();
                $sourceRecordedAt = ($data['source_recorded_at'] ?? null) === null
                    ? CarbonImmutable::now('UTC')
                    : CarbonImmutable::parse(
                        (string) $data['source_recorded_at'],
                        $sourceTimezone,
                    )->utc();
                $syncStatus = (bool) ($data['submitted_offline'] ?? false)
                    ? CareSyncStatus::Synchronized
                    : CareSyncStatus::Direct;
                $task = $this->task($lockedJournal, $data['care_task_id'] ?? null);

                $this->guardDuplicateFeeding(
                    $lockedJournal,
                    $type,
                    $startedAt,
                    (bool) ($data['confirm_duplicate'] ?? false),
                    $task,
                );

                $identity = $contributor ?? [
                    ...$this->actor->identity(),
                    'role' => $source->value,
                ];
                $entry = CareEntry::query()->create([
                    'care_journal_id' => $lockedJournal->id,
                    'care_task_id' => $task?->id,
                    'idempotency_key' => $data['idempotency_key'],
                    'type' => $type,
                    'subtype' => $data['subtype'] ?? null,
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                    'timezone' => $lockedJournal->timezone,
                    'source_recorded_at' => $sourceRecordedAt,
                    'source_timezone' => $sourceTimezone,
                    'sync_status' => $syncStatus,
                    'synchronized_at' => $syncStatus === CareSyncStatus::Synchronized
                        ? CarbonImmutable::now('UTC')
                        : null,
                    'status' => $status,
                    'source_type' => $source,
                    'source_name' => $data['source_name'] ?: $identity['name'],
                    'verification_status' => $source->verificationStatus(),
                    'author_key' => $identity['key'],
                    'author_name' => $identity['name'],
                    'title' => $data['title'],
                    'notes' => $data['notes'] ?? null,
                    'measurements' => $this->measurements($data),
                    'context' => $this->context($data),
                    'quantity_value' => $data['quantity_value'] ?? null,
                    'quantity_unit' => $data['quantity_unit'] ?? null,
                    'duration_minutes' => $data['duration_minutes'] ?? null,
                    'distance_meters' => $data['distance_meters'] ?? null,
                    'appetite' => $data['appetite'] ?? null,
                    'intensity' => $data['intensity'] ?? null,
                    'is_unusual' => (bool) ($data['is_unusual'] ?? false),
                    'privacy' => 'private',
                ]);

                if ($task !== null) {
                    $this->completeTask($task, $entry, $identity);
                }

                if (($data['media'] ?? null) instanceof UploadedFile) {
                    $storedPath = $this->storeMedia(
                        $lockedJournal,
                        $entry,
                        $data['media'],
                        $data['media_alt'] ?? null,
                        $identity['key'],
                    );
                }

                $this->updateLatest($lockedJournal, $entry);
                $lockedJournal->increment('lock_version');

                AuditLog::query()->create([
                    'actor_key' => $identity['key'],
                    'actor_role' => 'care-contributor-'.$source->value,
                    'action' => 'care-entry.created',
                    'target_type' => CareEntry::class,
                    'target_id' => (string) $entry->id,
                    'metadata' => [
                        'care_journal_id' => $lockedJournal->id,
                        'type' => $type->value,
                        'status' => $status->value,
                        'source_type' => $source->value,
                        'task_id' => $task?->id,
                        'has_media' => $storedPath !== null,
                        'sync_status' => $syncStatus->value,
                        'source_timezone' => $sourceTimezone,
                    ],
                ]);

                if ($accessGrant instanceof CareAccessGrant) {
                    AuditLog::query()->create([
                        'actor_key' => $identity['key'],
                        'actor_role' => $accessGrant->recipient_role,
                        'action' => 'care-access.entry-submitted',
                        'target_type' => CareAccessGrant::class,
                        'target_id' => (string) $accessGrant->id,
                        'metadata' => [
                            'care_journal_id' => $lockedJournal->id,
                            'care_entry_id' => $entry->id,
                            'sections' => $accessGrant->sections,
                            'views_used' => $accessGrant->views_used,
                            'max_views' => $accessGrant->max_views,
                        ],
                    ]);
                }

                return $entry;
            });
        } catch (Throwable $exception) {
            if ($storedPath !== null) {
                Storage::disk('local')->delete($storedPath);
            }

            throw $exception;
        }
    }

    private function task(CareJournal $journal, mixed $taskId): ?CareTask
    {
        if ($taskId === null || $taskId === '') {
            return null;
        }

        $task = CareTask::query()
            ->select([
                'id', 'care_journal_id', 'title', 'type', 'status',
                'due_at', 'timezone', 'priority', 'instructions',
            ])
            ->lockForUpdate()
            ->findOrFail((int) $taskId);

        if ($task->care_journal_id !== $journal->id) {
            throw ValidationException::withMessages([
                'care_task_id' => __('messages.this_task_does_not_belong_to_the_selected_care_journal'),
            ]);
        }

        if (! $task->status->isOpen()) {
            throw ValidationException::withMessages([
                'care_task_id' => __('messages.this_task_has_already_been_handled'),
            ]);
        }

        return $task;
    }

    private function guardDuplicateFeeding(
        CareJournal $journal,
        CareEntryType $type,
        CarbonImmutable $startedAt,
        bool $confirmed,
        ?CareTask $task,
    ): void {
        if ($type !== CareEntryType::Feeding || $confirmed || $task !== null) {
            return;
        }

        $recent = CareEntry::query()
            ->select(['id', 'author_name', 'started_at', 'status'])
            ->where('care_journal_id', $journal->id)
            ->where('type', CareEntryType::Feeding->value)
            ->whereIn('status', [
                CareEntryStatus::Completed->value,
                CareEntryStatus::Partial->value,
            ])
            ->whereBetween('started_at', [
                $startedAt->subHour(),
                $startedAt->addHour(),
            ])
            ->latest('started_at')
            ->first();

        if ($recent !== null) {
            throw ValidationException::withMessages([
                'confirm_duplicate' => sprintf(
                    (string) __('messages.care_feeding_duplicate'),
                    (string) $journal->pet_name,
                    (string) $recent->author_name,
                    (string) $recent->started_at?->format('H:i'),
                ),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function measurements(array $data): array
    {
        return array_filter([
            'product_name' => $data['product_name'] ?? null,
            'amount_offered' => $data['amount_offered'] ?? null,
            'amount_consumed' => $data['amount_consumed'] ?? null,
            'water_source' => $data['water_source'] ?? null,
            'toilet_quality' => $data['toilet_quality'] ?? null,
            'sleep_quality' => $data['sleep_quality'] ?? null,
            'temperature_c' => $data['temperature_c'] ?? null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function context(array $data): array
    {
        return array_filter([
            'location_label' => $data['location_label'] ?? null,
            'route_summary' => $data['route_summary'] ?? null,
            'mood' => $data['mood'] ?? null,
            'trigger' => $data['trigger'] ?? null,
            'result' => $data['result'] ?? null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /** @param array{key: string, name: string, role: string} $identity */
    private function completeTask(CareTask $task, CareEntry $entry, array $identity): void
    {
        $taskStatus = match ($entry->status) {
            CareEntryStatus::Completed => CareTaskStatus::Completed,
            CareEntryStatus::Partial => CareTaskStatus::Partial,
            CareEntryStatus::Refused => CareTaskStatus::Refused,
            CareEntryStatus::Skipped => CareTaskStatus::Missed,
            default => CareTaskStatus::NeedsHelp,
        };

        $task->forceFill([
            'status' => $taskStatus,
            'completed_at' => $entry->started_at,
            'completed_by_key' => $identity['key'],
            'completed_by_name' => $identity['name'],
            'completion_note' => $entry->notes,
        ])->save();
    }

    private function updateLatest(CareJournal $journal, CareEntry $entry): void
    {
        if (! in_array($entry->status, [
            CareEntryStatus::Completed,
            CareEntryStatus::Partial,
        ], true)) {
            return;
        }

        $column = match ($entry->type) {
            CareEntryType::Feeding => 'last_feeding_at',
            CareEntryType::Water => 'last_water_at',
            CareEntryType::Walk => 'last_walk_at',
            CareEntryType::Toilet => 'last_toilet_at',
            default => null,
        };

        if ($column !== null && (
            $journal->{$column} === null
            || $entry->started_at->isAfter($journal->{$column})
        )) {
            $journal->forceFill([$column => $entry->started_at])->save();
        }
    }

    private function storeMedia(
        CareJournal $journal,
        CareEntry $entry,
        UploadedFile $file,
        ?string $altText,
        string $actorKey,
    ): string {
        $path = $file->store('care-journals/'.$journal->id, 'local');

        CareMedia::query()->create([
            'care_journal_id' => $journal->id,
            'care_entry_id' => $entry->id,
            'disk' => 'local',
            'path' => $path,
            'mime_type' => (string) $file->getMimeType(),
            'original_name' => $file->getClientOriginalName(),
            'size_bytes' => $file->getSize(),
            'alt_text' => $altText,
            'sensitivity' => $entry->type === CareEntryType::Toilet
                ? 'sensitive'
                : 'private',
            'created_by_key' => $actorKey,
        ]);

        return $path;
    }
}
