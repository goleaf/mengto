<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumJournalMediaStatus;
use App\Models\ForumJournal;
use App\Models\ForumJournalEntry;
use App\Models\ForumJournalMedia;
use App\Models\User;
use App\Services\ForumJournalAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final readonly class StoreForumJournalMedia
{
    public function __construct(
        private Gate $gate,
        private ForumJournalAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumJournal $journal,
        ForumJournalEntry $entry,
        UploadedFile $upload,
        string $altText,
        ?string $caption,
        string $idempotencyKey,
    ): ForumJournalMedia {
        $this->gate->forUser($actor)->authorize('update', $journal);
        Validator::make([
            'upload' => $upload,
            'alt_text' => $altText,
            'caption' => $caption,
            'idempotency_key' => $idempotencyKey,
        ], [
            'upload' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:5120',
                'dimensions:min_width=32,min_height=32,max_width=12000,max_height=12000',
            ],
            'alt_text' => ['required', 'string', 'min:2', 'max:500'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        if ($entry->forum_journal_id !== $journal->id) {
            throw ValidationException::withMessages([
                'mediaForm.upload' => __('forum_journals.validation.entry_parent'),
            ]);
        }

        $existing = ForumJournalMedia::query()
            ->where('upload_idempotency_key', $idempotencyKey)
            ->first();

        if ($existing !== null) {
            if ($existing->forum_journal_entry_id !== $entry->id
                || $existing->uploaded_by_user_id !== $actor->id
            ) {
                throw ValidationException::withMessages([
                    'mediaForm.upload' => __('forum_journals.validation.idempotency_conflict'),
                ]);
            }

            return $existing;
        }

        $stableKey = 'journal-media-'.Str::lower((string) Str::ulid());
        $extension = $upload->guessExtension() ?: 'bin';
        $checksum = hash_file('sha256', $upload->getRealPath());
        $path = $upload->storeAs(
            "forum-journals/{$journal->stable_key}",
            "{$stableKey}.{$extension}",
            'local',
        );

        if (! is_string($path)) {
            throw ValidationException::withMessages([
                'mediaForm.upload' => __('forum_journals.validation.media_storage'),
            ]);
        }

        try {
            return DB::transaction(function () use (
                $actor,
                $altText,
                $caption,
                $checksum,
                $entry,
                $idempotencyKey,
                $journal,
                $path,
                $stableKey,
                $upload,
            ): ForumJournalMedia {
                $lockedJournal = ForumJournal::query()
                    ->lockForUpdate()
                    ->findOrFail($journal->id);
                $this->gate->forUser($actor)->authorize('update', $lockedJournal);
                ForumJournalEntry::query()
                    ->where('forum_journal_id', $lockedJournal->id)
                    ->findOrFail($entry->id);
                $media = ForumJournalMedia::query()->create([
                    'forum_journal_entry_id' => $entry->id,
                    'uploaded_by_user_id' => $actor->id,
                    'stable_key' => $stableKey,
                    'upload_idempotency_key' => $idempotencyKey,
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => Str::limit(
                        basename($upload->getClientOriginalName()),
                        255,
                        '',
                    ),
                    'mime_type' => $upload->getMimeType() ?: 'application/octet-stream',
                    'byte_size' => $upload->getSize(),
                    'checksum' => $checksum,
                    'alt_text' => trim($altText),
                    'caption' => filled($caption) ? trim((string) $caption) : null,
                    'status' => ForumJournalMediaStatus::Active,
                ]);
                $lockedJournal->increment('lock_version');
                $lockedJournal->topic()->update(['last_activity_at' => now()]);

                $this->audit->record($lockedJournal, $actor, 'forum-journal.media-added', [
                    'entry_id' => $entry->id,
                    'media_id' => $media->id,
                ]);

                return $media;
            }, 3);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }
    }
}
