<?php

declare(strict_types=1);

use App\Models\ContentMediaAsset;
use App\Models\ForumConfirmationEvidence;
use App\Models\ForumGroupFile;
use App\Models\MedicalDocument;
use App\Models\PetProfile;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileMedia;

test('private storage locators and idempotency keys are not serialized', function (
    string $modelClass,
    array $privateAttributes,
) {
    $model = $modelClass::factory()->make();
    $model->forceFill(array_fill_keys($privateAttributes, 'private-fixture-value'));
    $serialized = $model->toArray();

    expect(array_intersect($privateAttributes, array_keys($serialized)))->toBeEmpty();
})->with([
    'content media storage' => [
        ContentMediaAsset::class,
        ['disk', 'path', 'original_name', 'checksum_sha256'],
    ],
    'group file storage' => [
        ForumGroupFile::class,
        ['disk', 'path', 'original_name', 'checksum', 'upload_idempotency_key'],
    ],
    'confirmation private evidence' => [
        ForumConfirmationEvidence::class,
        ['private_disk', 'private_path'],
    ],
    'profile media upload identity' => [
        PetProfileMedia::class,
        ['current_key', 'upload_key'],
    ],
    'pet creation identity' => [PetProfile::class, ['creation_key']],
    'pet lifecycle idempotency' => [PetProfileLifecycleEvent::class, ['idempotency_key']],
    'medical document storage name' => [MedicalDocument::class, ['original_name']],
]);
