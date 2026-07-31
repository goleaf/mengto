<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\MedicalAccessGrant;
use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use App\Services\ForumActor;
use App\Services\PrivateFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrepareMedicalDocumentDownload
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly PrivateFileResponse $privateFiles,
    ) {}

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
        $response = $this->privateFiles->download(
            disk: 'local',
            path: $document->file_path,
            allowedDirectory: 'medical-records/'.$document->medical_record_id,
            downloadName: $document->original_name,
            headers: ['Content-Type' => $document->mime_type],
        );

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

        return $response;
    }

    private function assertDocumentBelongsToRecord(
        MedicalRecord $record,
        MedicalDocument $document,
    ): void {
        if ($document->medical_record_id !== $record->id) {
            abort(404);
        }
    }
}
