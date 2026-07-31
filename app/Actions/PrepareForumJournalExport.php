<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumJournal;
use App\Models\ForumJournalEntry;
use App\Models\User;
use App\Services\ForumJournalAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class PrepareForumJournalExport
{
    public function __construct(
        private Gate $gate,
        private ForumJournalAudit $audit,
    ) {}

    public function handle(User $actor, ForumJournal $journal): StreamedResponse
    {
        $this->gate->forUser($actor)->authorize('export', $journal);
        $journal->load('topic:id,title,slug,language,visibility');
        $this->audit->record($journal, $actor, 'forum-journal.exported');
        $header = [
            'schema_version' => 1,
            'journal' => [
                'stable_key' => $journal->stable_key,
                'type' => $journal->type->value,
                'status' => $journal->status->value,
                'started_on' => $journal->started_on->toDateString(),
                'timezone' => $journal->timezone,
                'title' => $journal->topic?->title,
                'language' => $journal->topic?->language,
                'visibility' => $journal->topic?->visibility->value,
            ],
            'exported_at' => now()->toIso8601String(),
        ];
        $filename = sprintf(
            '%s-journal.json',
            Str::slug($journal->topic->title),
        );

        return response()->streamDownload(
            function () use ($header, $journal): void {
                echo '{"schema_version":';
                echo json_encode($header['schema_version'], JSON_THROW_ON_ERROR);
                echo ',"journal":';
                echo json_encode($header['journal'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                echo ',"exported_at":';
                echo json_encode($header['exported_at'], JSON_THROW_ON_ERROR);
                echo ',"entries":[';
                $first = true;

                ForumJournalEntry::query()
                    ->forTimeline()
                    ->where('forum_journal_id', $journal->id)
                    ->with([
                        'measurements:id,forum_journal_entry_id,metric_key,numeric_value,unit,position',
                        'comments' => fn ($comments) => $comments
                            ->forJournalEntry()
                            ->orderBy('created_at')
                            ->orderBy('id'),
                        'media:id,forum_journal_entry_id,stable_key,mime_type,byte_size,alt_text,caption,status,created_at',
                    ])
                    ->orderBy('id')
                    ->chunkById(100, function ($entries) use (&$first): void {
                        foreach ($entries as $entry) {
                            if (! $first) {
                                echo ',';
                            }

                            $first = false;
                            echo json_encode([
                                'stable_key' => $entry->stable_key,
                                'kind' => $entry->kind->value,
                                'occurred_at' => $entry->occurred_at->toIso8601String(),
                                'timezone' => $entry->timezone,
                                'title' => $entry->title,
                                'body' => $entry->body,
                                'author_name' => $entry->author_name,
                                'measurements' => $entry->measurements
                                    ->map(static fn ($measurement): array => [
                                        'metric_key' => $measurement->metric_key,
                                        'numeric_value' => $measurement->numeric_value,
                                        'unit' => $measurement->unit,
                                    ])
                                    ->all(),
                                'comments' => $entry->comments
                                    ->map(static fn ($comment): array => [
                                        'author_name' => $comment->author_name,
                                        'body' => $comment->body,
                                        'created_at' => $comment->created_at?->toIso8601String(),
                                    ])
                                    ->all(),
                                'media' => $entry->media
                                    ->map(static fn ($media): array => [
                                        'stable_key' => $media->stable_key,
                                        'mime_type' => $media->mime_type,
                                        'byte_size' => $media->byte_size,
                                        'alt_text' => $media->alt_text,
                                        'caption' => $media->caption,
                                    ])
                                    ->all(),
                            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                        }
                    });

                echo ']}';
            },
            $filename,
            [
                'Content-Type' => 'application/json; charset=UTF-8',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
