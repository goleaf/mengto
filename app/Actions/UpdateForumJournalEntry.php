<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\UpdateForumJournalEntryData;
use App\Enums\ForumJournalEntryKind;
use App\Models\ForumJournal;
use App\Models\ForumJournalEntry;
use App\Models\ForumJournalEntryVersion;
use App\Models\User;
use App\Services\ForumJournalAudit;
use App\Services\ForumJournalMetricRegistry;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class UpdateForumJournalEntry
{
    public function __construct(
        private Gate $gate,
        private ForumJournalMetricRegistry $metrics,
        private ForumJournalAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumJournal $journal,
        ForumJournalEntry $entry,
        UpdateForumJournalEntryData $data,
    ): ForumJournalEntry {
        $this->gate->forUser($actor)->authorize('update', $journal);
        $this->validate($data);
        $measurements = $this->metrics->normalize($journal->type, $data->measurements);

        return DB::transaction(function () use (
            $actor,
            $data,
            $entry,
            $journal,
            $measurements,
        ): ForumJournalEntry {
            $lockedJournal = ForumJournal::query()
                ->lockForUpdate()
                ->findOrFail($journal->id);
            $this->gate->forUser($actor)->authorize('update', $lockedJournal);
            $lockedEntry = ForumJournalEntry::query()
                ->with('measurements')
                ->where('forum_journal_id', $lockedJournal->id)
                ->lockForUpdate()
                ->findOrFail($entry->id);

            if ($lockedEntry->lock_version !== $data->expectedVersion) {
                throw ValidationException::withMessages([
                    'entryForm.title' => __('forum_journals.validation.entry_changed'),
                ]);
            }

            ForumJournalEntryVersion::query()->create([
                'forum_journal_entry_id' => $lockedEntry->id,
                'edited_by_user_id' => $actor->id,
                'version' => $lockedEntry->lock_version,
                'snapshot' => [
                    'kind' => $lockedEntry->kind->value,
                    'title' => $lockedEntry->title,
                    'body' => $lockedEntry->body,
                    'occurred_at' => $lockedEntry->occurred_at->toIso8601String(),
                    'timezone' => $lockedEntry->timezone,
                    'measurements' => $lockedEntry->measurements
                        ->map(static fn ($measurement): array => [
                            'metric_key' => $measurement->metric_key,
                            'numeric_value' => $measurement->numeric_value,
                            'unit' => $measurement->unit,
                            'position' => $measurement->position,
                        ])
                        ->all(),
                ],
                'reason_code' => 'author-edit',
                'created_at' => now(),
            ]);

            $lockedEntry->forceFill([
                'kind' => $data->kind,
                'title' => trim($data->title),
                'body' => trim($data->body),
                'occurred_at' => $data->occurredAt,
                'timezone' => $data->timezone,
                'lock_version' => $lockedEntry->lock_version + 1,
            ])->save();
            $lockedEntry->measurements()->delete();
            $lockedEntry->measurements()->createMany($measurements);
            $lockedJournal->increment('lock_version');
            $lockedJournal->topic()->update(['last_activity_at' => now()]);

            $this->audit->record($lockedJournal, $actor, 'forum-journal.entry-updated', [
                'entry_id' => $lockedEntry->id,
                'entry_version' => $lockedEntry->lock_version,
            ]);

            return $lockedEntry->refresh()->load('measurements');
        }, 3);
    }

    private function validate(UpdateForumJournalEntryData $data): void
    {
        Validator::make([
            'kind' => $data->kind->value,
            'title' => $data->title,
            'body' => $data->body,
            'occurred_at' => $data->occurredAt->toIso8601String(),
            'timezone' => $data->timezone,
            'measurements' => $data->measurements,
            'expected_version' => $data->expectedVersion,
        ], [
            'kind' => ['required', Rule::enum(ForumJournalEntryKind::class)],
            'title' => ['required', 'string', 'min:2', 'max:180'],
            'body' => ['required', 'string', 'min:2', 'max:10000'],
            'occurred_at' => ['required', 'date', 'before_or_equal:+5 minutes'],
            'timezone' => ['required', 'timezone:all'],
            'measurements' => ['array', 'max:8'],
            'expected_version' => ['required', 'integer', 'min:0'],
        ])->validate();
    }
}
