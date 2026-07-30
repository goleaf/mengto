<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use App\Services\ForumActor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StoreMedicalDocument
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(MedicalRecord $record, array $data): MedicalDocument
    {
        return DB::transaction(function () use ($record, $data): MedicalDocument {
            /** @var UploadedFile $file */
            $file = $data['document'];
            $path = $file->store('medical-records/'.$record->id, 'local');
            $document = $record->documents()->create([
                'type' => $data['document_type'],
                'title' => $data['title'],
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size_bytes' => $file->getSize(),
                'source_type' => $data['source_type'],
                'source_name' => $data['source_name'] ?: $this->actor->identity()['name'],
                'verification_status' => $data['source_type'] === 'owner'
                    ? 'owner-reported'
                    : 'needs-review',
                'expires_on' => $data['expires_on'] ?? null,
                'uploaded_by_key' => $this->actor->key(),
            ]);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'medical-record-owner',
                'action' => 'medical-document.uploaded',
                'target_type' => MedicalDocument::class,
                'target_id' => (string) $document->id,
                'metadata' => [
                    'medical_record_id' => $record->id,
                    'document_type' => $document->type,
                    'mime_type' => $document->mime_type,
                    'size_bytes' => $document->size_bytes,
                ],
            ]);

            return $document;
        });
    }
}
