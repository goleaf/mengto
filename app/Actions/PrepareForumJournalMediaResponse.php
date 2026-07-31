<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumJournal;
use App\Models\ForumJournalMedia;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class PrepareForumJournalMediaResponse
{
    public function __construct(private Gate $gate) {}

    public function handle(
        ForumJournal $journal,
        ForumJournalMedia $media,
    ): StreamedResponse {
        $this->gate->authorize('view', $media);

        $belongsToJournal = $media->entry()
            ->where('forum_journal_id', $journal->id)
            ->exists();

        if (! $belongsToJournal) {
            abort(404);
        }

        if (! Storage::disk($media->disk)->exists($media->path)) {
            abort(404);
        }

        return Storage::disk($media->disk)->response(
            $media->path,
            null,
            [
                'Content-Type' => $media->mime_type,
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
