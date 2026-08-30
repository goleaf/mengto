<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumComment;
use App\Models\ForumJournal;
use App\Models\ForumJournalEntry;
use App\Models\ForumJournalMeasurement;
use App\Models\ForumJournalMedia;
use App\Models\User;
use App\Services\ForumJournalAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;
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
                    ->orderBy('id')
                    ->chunkById(100, function (Collection $entries) use (&$first): void {
                        $entryIds = $entries->modelKeys();
                        $measurements = $this->spoolRelated(
                            ForumJournalMeasurement::query()
                                ->select([
                                    'id',
                                    'forum_journal_entry_id',
                                    'metric_key',
                                    'numeric_value',
                                    'unit',
                                    'position',
                                ])
                                ->whereIn('forum_journal_entry_id', $entryIds)
                                ->orderBy('forum_journal_entry_id')
                                ->orderBy('position')
                                ->orderBy('id')
                                ->cursor(),
                            static fn (ForumJournalMeasurement $measurement): array => [
                                'metric_key' => $measurement->metric_key,
                                'numeric_value' => $measurement->numeric_value,
                                'unit' => $measurement->unit,
                            ],
                        );
                        $comments = $this->spoolRelated(
                            ForumComment::query()
                                ->forJournalEntry()
                                ->whereIn('forum_journal_entry_id', $entryIds)
                                ->orderBy('forum_journal_entry_id')
                                ->orderBy('created_at')
                                ->orderBy('id')
                                ->cursor(),
                            static fn (ForumComment $comment): array => [
                                'author_name' => $comment->author_name,
                                'body' => $comment->body,
                                'created_at' => $comment->created_at?->toIso8601String(),
                            ],
                        );
                        $media = $this->spoolRelated(
                            ForumJournalMedia::query()
                                ->select([
                                    'id',
                                    'forum_journal_entry_id',
                                    'stable_key',
                                    'mime_type',
                                    'byte_size',
                                    'alt_text',
                                    'caption',
                                ])
                                ->whereIn('forum_journal_entry_id', $entryIds)
                                ->orderBy('forum_journal_entry_id')
                                ->orderBy('id')
                                ->cursor(),
                            static fn (ForumJournalMedia $item): array => [
                                'stable_key' => $item->stable_key,
                                'mime_type' => $item->mime_type,
                                'byte_size' => $item->byte_size,
                                'alt_text' => $item->alt_text,
                                'caption' => $item->caption,
                            ],
                        );

                        foreach ($entries as $entry) {
                            if (! $first) {
                                echo ',';
                            }

                            $first = false;
                            $encoded = json_encode([
                                'stable_key' => $entry->stable_key,
                                'kind' => $entry->kind->value,
                                'occurred_at' => $entry->occurred_at->toIso8601String(),
                                'timezone' => $entry->timezone,
                                'title' => $entry->title,
                                'body' => $entry->body,
                                'author_name' => $entry->author_name,
                            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                            echo substr($encoded, 0, -1);
                            echo ',"measurements":[';
                            $this->streamSpooled($measurements, $entry->id);
                            echo '],"comments":[';
                            $this->streamSpooled($comments, $entry->id);
                            echo '],"media":[';
                            $this->streamSpooled($media, $entry->id);
                            echo ']}';
                        }

                        fclose($measurements);
                        fclose($comments);
                        fclose($media);
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

    /**
     * @param  iterable<int, Model>  $items
     * @param  callable(Model): array<string, mixed>  $present
     * @return resource
     */
    private function spoolRelated(iterable $items, callable $present)
    {
        $stream = tmpfile();

        if ($stream === false) {
            throw new RuntimeException(__('messages.unable_to_allocate_the_journal_export_spool'));
        }

        foreach ($items as $item) {
            $encoded = json_encode([
                'entry_id' => (int) $item->forum_journal_entry_id,
                'payload' => $present($item),
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)."\n";

            if (fwrite($stream, $encoded) === false) {
                fclose($stream);

                throw new RuntimeException(__('messages.unable_to_write_the_journal_export_spool'));
            }
        }

        rewind($stream);

        return $stream;
    }

    /** @param resource $stream */
    private function streamSpooled($stream, int $entryId): void
    {
        $first = true;

        while (($position = ftell($stream)) !== false && ($line = fgets($stream)) !== false) {
            $item = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
            $itemEntryId = (int) $item['entry_id'];

            if ($itemEntryId < $entryId) {
                continue;
            }

            if ($itemEntryId > $entryId) {
                fseek($stream, $position);

                break;
            }

            if (! $first) {
                echo ',';
            }

            $first = false;
            echo json_encode(
                $item['payload'],
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE,
            );
        }
    }
}
