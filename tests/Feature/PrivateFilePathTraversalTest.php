<?php

declare(strict_types=1);

use App\Models\AuditLog;
use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use App\Services\PrivateFileResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('private file responses allow regular files inside the owning directory', function () {
    Storage::fake('local');
    Storage::disk('local')->put('medical-records/42/report.pdf', 'private report');

    $response = app(PrivateFileResponse::class)->download(
        disk: 'local',
        path: 'medical-records/42/report.pdf',
        allowedDirectory: 'medical-records/42',
        downloadName: 'report.pdf',
        headers: ['Content-Type' => 'application/pdf'],
    );

    expect($response->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($response->headers->get('Content-Disposition'))->toContain('attachment');
});

test('private file responses reject traversal and absolute path representations', function (
    string $path,
) {
    Storage::fake('local');

    expect(fn () => app(PrivateFileResponse::class)->download(
        disk: 'local',
        path: $path,
        allowedDirectory: 'medical-records/42',
        downloadName: 'report.pdf',
    ))->toThrow(NotFoundHttpException::class);
})->with([
    'parent segment' => '../.env',
    'nested parent segments' => 'medical-records/42/../../.env',
    'windows separators' => 'medical-records\\42\\..\\..\\.env',
    'absolute path' => '/etc/passwd',
    'sibling prefix' => 'medical-records/420/report.pdf',
]);

test('private file responses reject existing files from another private domain', function () {
    Storage::fake('local');
    Storage::disk('local')->put('care-journals/9/private.jpg', 'private care image');

    expect(fn () => app(PrivateFileResponse::class)->download(
        disk: 'local',
        path: 'care-journals/9/private.jpg',
        allowedDirectory: 'medical-records/42',
        downloadName: 'private.jpg',
    ))->toThrow(NotFoundHttpException::class);
});

test('private file responses reject missing files and non-private disks', function () {
    Storage::fake('local');
    Storage::fake('public');
    Storage::disk('public')->put('medical-records/42/report.pdf', 'public copy');

    expect(fn () => app(PrivateFileResponse::class)->download(
        disk: 'local',
        path: 'medical-records/42/missing.pdf',
        allowedDirectory: 'medical-records/42',
        downloadName: 'missing.pdf',
    ))->toThrow(NotFoundHttpException::class)
        ->and(fn () => app(PrivateFileResponse::class)->download(
            disk: 'public',
            path: 'medical-records/42/report.pdf',
            allowedDirectory: 'medical-records/42',
            downloadName: 'report.pdf',
        ))->toThrow(NotFoundHttpException::class);
});

test('private file responses reject symbolic links escaping the owning directory', function () {
    Storage::fake('local');
    Storage::disk('local')->put('care-journals/9/private.jpg', 'private care image');
    Storage::disk('local')->makeDirectory('medical-records/42');

    $created = symlink(
        Storage::disk('local')->path('care-journals/9/private.jpg'),
        Storage::disk('local')->path('medical-records/42/linked.jpg'),
    );

    expect($created)->toBeTrue()
        ->and(fn () => app(PrivateFileResponse::class)->download(
            disk: 'local',
            path: 'medical-records/42/linked.jpg',
            allowedDirectory: 'medical-records/42',
            downloadName: 'linked.jpg',
        ))->toThrow(NotFoundHttpException::class);
});

test('medical downloads fail closed before auditing unsafe stored paths', function (
    string $case,
) {
    Storage::fake('local');
    $record = MedicalRecord::factory()->create(['owner_key' => 'test-member']);
    $path = match ($case) {
        'traversal' => "medical-records/{$record->id}/../../care-journals/leak.pdf",
        'cross-domain' => 'care-journals/foreign/leak.pdf',
    };
    $document = MedicalDocument::factory()->for($record)->create([
        'file_path' => $path,
        'download_count' => 0,
    ]);

    if ($case === 'cross-domain') {
        Storage::disk('local')->put($path, 'foreign private file');
    }

    $this->get(route('medical-records.documents.download', [$record, $document]))
        ->assertNotFound();

    expect($document->refresh()->download_count)->toBe(0)
        ->and(AuditLog::query()
            ->where('action', 'medical-document.downloaded')
            ->where('target_id', (string) $document->id)
            ->exists())
        ->toBeFalse();
})->with(['traversal', 'cross-domain']);
