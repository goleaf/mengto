<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumJournal;
use App\Models\ForumJournalMedia;
use App\Services\PrivateFileResponse;
use Illuminate\Contracts\Auth\Access\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class PrepareForumJournalMediaResponse
{
    public function __construct(
        private Gate $gate,
        private PrivateFileResponse $privateFiles,
    ) {}

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

        return $this->privateFiles->inline(
            disk: $media->disk,
            path: $media->path,
            allowedDirectory: 'forum-journals/'.$journal->stable_key,
            headers: [
                'Content-Type' => $media->mime_type,
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
