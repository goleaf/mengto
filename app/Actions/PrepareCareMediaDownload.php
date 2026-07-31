<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\CareAccessGrant;
use App\Models\CareEntry;
use App\Models\CareJournal;
use App\Models\CareMedia;
use App\Services\ForumActor;
use App\Services\PrivateFileResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrepareCareMediaDownload
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly PrivateFileResponse $privateFiles,
    ) {}

    public function forOwner(CareJournal $journal, CareMedia $media): StreamedResponse
    {
        $this->assertMediaBelongsToJournal($journal, $media);

        return $this->download($media, $this->actor->key(), 'care-journal-owner');
    }

    public function forGrant(
        CareAccessGrant $grant,
        CareMedia $media,
    ): StreamedResponse {
        if (! $grant->allow_media) {
            abort(403);
        }

        $this->assertMediaBelongsToJournal($grant->careJournal, $media);
        $entry = CareEntry::query()
            ->select(['id', 'care_journal_id', 'type'])
            ->findOrFail($media->care_entry_id);

        if (! $grant->canViewSection($entry->type->section())) {
            abort(403);
        }

        return $this->download(
            $media,
            $grant->recipient_key ?? 'temporary-link',
            $grant->recipient_role,
        );
    }

    private function download(
        CareMedia $media,
        string $actorKey,
        string $actorRole,
    ): StreamedResponse {
        $response = $this->privateFiles->download(
            disk: $media->disk,
            path: $media->path,
            allowedDirectory: 'care-journals/'.$media->care_journal_id,
            downloadName: $media->original_name,
            headers: ['Content-Type' => $media->mime_type],
        );

        AuditLog::query()->create([
            'actor_key' => $actorKey,
            'actor_role' => $actorRole,
            'action' => 'care-media.downloaded',
            'target_type' => CareMedia::class,
            'target_id' => (string) $media->id,
            'metadata' => [
                'care_journal_id' => $media->care_journal_id,
                'mime_type' => $media->mime_type,
                'sensitivity' => $media->sensitivity,
            ],
        ]);

        return $response;
    }

    private function assertMediaBelongsToJournal(
        CareJournal $journal,
        CareMedia $media,
    ): void {
        if ($media->care_journal_id !== $journal->id) {
            throw ValidationException::withMessages([
                'media' => __('messages.this_file_does_not_belong_to_the_selected_care_journal_e162111eec'),
            ]);
        }
    }
}
