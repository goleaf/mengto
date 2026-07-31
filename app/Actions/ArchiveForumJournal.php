<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumJournalStatus;
use App\Enums\ForumTopicStatus;
use App\Models\ForumJournal;
use App\Models\ForumTopic;
use App\Models\User;
use App\Services\ForumJournalAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class ArchiveForumJournal
{
    public function __construct(
        private Gate $gate,
        private ForumJournalAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumJournal $journal,
        int $expectedVersion,
        string $reasonCode = 'owner-archive',
    ): ForumJournal {
        $this->gate->forUser($actor)->authorize('archive', $journal);
        Validator::make([
            'expected_version' => $expectedVersion,
            'reason_code' => $reasonCode,
        ], [
            'expected_version' => ['required', 'integer', 'min:0'],
            'reason_code' => ['required', 'string', 'max:80', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $expectedVersion,
            $journal,
            $reasonCode,
        ): ForumJournal {
            $locked = ForumJournal::query()
                ->with('topic')
                ->lockForUpdate()
                ->findOrFail($journal->id);
            $this->gate->forUser($actor)->authorize('archive', $locked);

            if ($locked->lock_version !== $expectedVersion) {
                throw ValidationException::withMessages([
                    'journal' => __('forum_journals.validation.journal_changed'),
                ]);
            }

            $topic = ForumTopic::query()
                ->lockForUpdate()
                ->findOrFail($locked->forum_topic_id);
            $previousTopicStatus = $topic->status->value;
            $metadata = $locked->metadata ?? [];
            $locked->forceFill([
                'status' => ForumJournalStatus::Archived,
                'archived_by_user_id' => $actor->id,
                'archived_at' => now(),
                'archive_reason_code' => $reasonCode,
                'lock_version' => $locked->lock_version + 1,
                'metadata' => [
                    ...$metadata,
                    'pre_archive_topic_status' => $previousTopicStatus,
                ],
            ])->save();
            $topic->forceFill([
                'status' => ForumTopicStatus::Archived,
                'archived_at' => now(),
                'is_locked' => true,
                'lock_version' => $topic->lock_version + 1,
            ])->save();

            $this->audit->record($locked, $actor, 'forum-journal.archived', [
                'reason_code' => $reasonCode,
            ]);

            return $locked->refresh()->load('topic');
        }, 3);
    }
}
