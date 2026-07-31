<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumComment;
use App\Models\ForumJournal;
use App\Models\ForumJournalEntry;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class CreateForumJournalComment
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        ForumJournal $journal,
        ForumJournalEntry $entry,
        string $body,
        string $idempotencyKey,
    ): ForumComment {
        $this->gate->forUser($actor)->authorize('comment', $journal);
        Validator::make([
            'body' => $body,
            'idempotency_key' => $idempotencyKey,
        ], [
            'body' => ['required', 'string', 'min:2', 'max:1500'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $body,
            $entry,
            $idempotencyKey,
            $journal,
        ): ForumComment {
            $lockedJournal = ForumJournal::query()
                ->with('topic')
                ->lockForUpdate()
                ->findOrFail($journal->id);
            $this->gate->forUser($actor)->authorize('comment', $lockedJournal);
            $lockedEntry = ForumJournalEntry::query()
                ->where('forum_journal_id', $lockedJournal->id)
                ->findOrFail($entry->id);
            $existing = ForumComment::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing !== null) {
                if ($existing->forum_journal_entry_id !== $lockedEntry->id
                    || $existing->author_id !== $actor->id
                ) {
                    throw ValidationException::withMessages([
                        'commentForm.body' => __('forum_journals.validation.idempotency_conflict'),
                    ]);
                }

                return $existing;
            }

            $comment = ForumComment::query()->create([
                'topic_id' => $lockedJournal->forum_topic_id,
                'answer_id' => null,
                'forum_journal_entry_id' => $lockedEntry->id,
                'parent_id' => null,
                'author_id' => $actor->id,
                'author_key' => $actor->actor_key,
                'author_name' => $actor->name,
                'author_initials' => $this->initials($actor->name),
                'body' => trim($body),
                'status' => $lockedJournal->topic?->comment_policy === 'review'
                    ? 'review'
                    : 'published',
                'is_pinned' => false,
                'idempotency_key' => $idempotencyKey,
            ]);
            $lockedJournal->increment('lock_version');
            $lockedJournal->topic()->update(['last_activity_at' => now()]);

            return $comment;
        }, 3);
    }

    private function initials(string $name): string
    {
        return Str::of($name)
            ->split('/\s+/')
            ->filter()
            ->take(2)
            ->map(static fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }
}
