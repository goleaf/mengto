<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\MedicalAccessGrant;
use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use App\Services\ForumActor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrepareMedicalDocumentDownload
{
    public function __construct(private readonly ForumActor $actor) {}

    public function forOwner(MedicalRecord $record, MedicalDocument $document): StreamedResponse
    {
        $this->assertDocumentBelongsToRecord($record, $document);

        return $this->download($document, $this->actor->key(), 'medical-record-owner');
    }

    public function forGrant(
        MedicalAccessGrant $grant,
        MedicalDocument $document,
    ): StreamedResponse {
        if (! $grant->allow_download || ! $grant->canViewSection('documents')) {
            abort(403);
        }

        $this->assertDocumentBelongsToRecord($grant->medicalRecord, $document);

        return $this->download(
            $document,
            $grant->recipient_key ?? 'temporary-link',
            $grant->recipient_role,
        );
    }

    private function download(
        MedicalDocument $document,
        string $actorKey,
        string $actorRole,
    ): StreamedResponse {
        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404);
        }

        $document->increment('download_count');

        AuditLog::query()->create([
            'actor_key' => $actorKey,
            'actor_role' => $actorRole,
            'action' => 'medical-document.downloaded',
            'target_type' => MedicalDocument::class,
            'target_id' => (string) $document->id,
            'metadata' => [
                'medical_record_id' => $document->medical_record_id,
                'mime_type' => $document->mime_type,
            ],
        ]);

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name,
            ['Content-Type' => $document->mime_type],
        );
    }

    private function assertDocumentBelongsToRecord(
        MedicalRecord $record,
        MedicalDocument $document,
    ): void {
        if ($document->medical_record_id !== $record->id) {
            throw ValidationException::withMessages([
                'document' => __('messages.this_document_does_not_belong_to_the_selected_medical_re_f2729f2963'),
            ]);
        }
    }
}
