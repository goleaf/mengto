<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateForumJournalEntryData;
use App\Enums\ForumJournalEntryKind;
use App\Models\ForumJournal;
use App\Models\ForumJournalEntry;
use App\Models\User;
use App\Services\ForumJournalAudit;
use App\Services\ForumJournalMetricRegistry;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class CreateForumJournalEntry
{
    public function __construct(
        private Gate $gate,
        private ForumJournalMetricRegistry $metrics,
        private ForumJournalAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumJournal $journal,
        CreateForumJournalEntryData $data,
    ): ForumJournalEntry {
        $this->gate->forUser($actor)->authorize('update', $journal);
        $this->validate($data);
        $measurements = $this->metrics->normalize($journal->type, $data->measurements);

        return DB::transaction(function () use (
            $actor,
            $data,
            $journal,
            $measurements,
        ): ForumJournalEntry {
            $lockedJournal = ForumJournal::query()
                ->lockForUpdate()
                ->findOrFail($journal->id);
            $this->gate->forUser($actor)->authorize('update', $lockedJournal);

            $existing = ForumJournalEntry::query()
                ->where('idempotency_key', $data->idempotencyKey)
                ->first();

            if ($existing !== null) {
                if ($existing->forum_journal_id !== $lockedJournal->id
                    || $existing->author_user_id !== $actor->id
                ) {
                    throw ValidationException::withMessages([
                        'entryForm.title' => __('forum_journals.validation.idempotency_conflict'),
                    ]);
                }

                return $existing->load('measurements');
            }

            $entry = ForumJournalEntry::query()->create([
                'forum_journal_id' => $lockedJournal->id,
                'author_user_id' => $actor->id,
                'author_key' => $actor->actor_key,
                'author_name' => $actor->name,
                'stable_key' => 'journal-entry-'.Str::lower((string) Str::ulid()),
                'idempotency_key' => $data->idempotencyKey,
                'kind' => $data->kind,
                'occurred_at' => $data->occurredAt,
                'timezone' => $data->timezone,
                'title' => trim($data->title),
                'body' => trim($data->body),
                'lock_version' => 0,
            ]);
            $entry->measurements()->createMany($measurements);
            $lockedJournal->increment('lock_version');
            $lockedJournal->topic()->update(['last_activity_at' => now()]);

            $this->audit->record($lockedJournal, $actor, 'forum-journal.entry-created', [
                'entry_id' => $entry->id,
                'entry_kind' => $data->kind->value,
            ]);

            return $entry->load('measurements');
        }, 3);
    }

    private function validate(CreateForumJournalEntryData $data): void
    {
        Validator::make([
            'kind' => $data->kind->value,
            'title' => $data->title,
            'body' => $data->body,
            'occurred_at' => $data->occurredAt->toIso8601String(),
            'timezone' => $data->timezone,
            'measurements' => $data->measurements,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'kind' => ['required', Rule::enum(ForumJournalEntryKind::class)],
            'title' => ['required', 'string', 'min:2', 'max:180'],
            'body' => ['required', 'string', 'min:2', 'max:10000'],
            'occurred_at' => ['required', 'date', 'before_or_equal:+5 minutes'],
            'timezone' => ['required', 'timezone:all'],
            'measurements' => ['array', 'max:8'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
    }
}
