<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ContentMediaStatus;
use App\Enums\ContentMediaType;
use App\Enums\PlaceMediaStatus;
use App\Enums\PlaceMediaVariant;
use App\Enums\PlaceMediaVariantStatus;
use App\Models\ContentMediaAsset;
use App\Models\Place;
use App\Models\PlaceMedia;
use App\Models\PlaceMediaEvent;
use App\Models\PlaceMediaVariant as PlaceMediaVariantModel;
use App\Models\User;
use App\Validation\PlaceMediaRules;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Image\ImageException;
use Illuminate\Image\ImageManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

final readonly class StorePlaceMedia
{
    public function __construct(
        private Gate $gate,
        private ImageManager $images,
        private Repository $config,
    ) {}

    public function handle(
        User $actor,
        Place $place,
        UploadedFile $upload,
        string $altText,
        string $attribution,
        string $licence,
        string $idempotencyKey,
    ): PlaceMedia {
        $this->gate->forUser($actor)->authorize('manageMedia', $place);
        Validator::make(compact(
            'upload', 'altText', 'attribution', 'licence', 'idempotencyKey',
        ), [
            'upload' => PlaceMediaRules::upload(),
            'altText' => ['required', 'string', 'min:2', 'max:500'],
            'attribution' => ['required', 'string', 'min:2', 'max:500'],
            'licence' => ['required', Rule::in([
                'all-rights-reserved', 'cc-by', 'cc-by-sa', 'public-domain',
            ])],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $uploadKey = hash('sha256', "place-media|{$place->id}|{$actor->id}|{$idempotencyKey}");
        $existing = PlaceMedia::query()->with(['asset', 'variants'])->where('upload_key', $uploadKey)->first();

        if ($existing instanceof PlaceMedia) {
            return $this->validateReplay($existing, $place, $actor);
        }

        $mediaKey = Str::lower((string) Str::ulid());
        $directory = "place-media/{$place->stable_key}/{$mediaKey}";
        $stagingDirectory = "place-media/staging/{$actor->id}";
        $stagingPath = Storage::disk('local')->putFileAs(
            $stagingDirectory,
            $upload,
            "{$mediaKey}.upload",
        );

        if (! is_string($stagingPath)) {
            $this->fail();
        }

        try {
            $storedVariants = $this->storeVariants($upload, $directory);
        } finally {
            Storage::disk('local')->delete($stagingPath);
        }

        try {
            return DB::transaction(function () use (
                $actor,
                $altText,
                $attribution,
                $directory,
                $licence,
                $mediaKey,
                $place,
                $storedVariants,
                $upload,
                $uploadKey,
            ): PlaceMedia {
                $lockedPlace = Place::query()
                    ->with('organization.activeMemberships')
                    ->lockForUpdate()
                    ->findOrFail($place->id);
                $this->gate->forUser($actor)->authorize('manageMedia', $lockedPlace);
                $existing = PlaceMedia::query()->with(['asset', 'variants'])
                    ->where('upload_key', $uploadKey)
                    ->first();

                if ($existing instanceof PlaceMedia) {
                    Storage::disk('local')->deleteDirectory($directory);

                    return $this->validateReplay($existing, $lockedPlace, $actor);
                }

                $count = PlaceMedia::query()
                    ->where('place_id', $lockedPlace->id)
                    ->whereIn('status', [
                        PlaceMediaStatus::PendingReview->value,
                        PlaceMediaStatus::Active->value,
                    ])
                    ->count();

                if ($count >= $this->config->integer('images.place_uploads.max_active_per_place')) {
                    throw ValidationException::withMessages([
                        'upload' => __('places.media.validation.limit'),
                    ]);
                }

                $fallback = $storedVariants[PlaceMediaVariant::Fallback->value];
                $asset = ContentMediaAsset::query()->create([
                    'media_key' => (string) Str::ulid(),
                    'owner_user_id' => $actor->id,
                    'created_by_user_id' => $actor->id,
                    'media_type' => ContentMediaType::Image,
                    'status' => ContentMediaStatus::Ready,
                    'disk' => 'local',
                    'path' => $fallback['path'],
                    'original_name' => $this->originalName($upload),
                    'mime_type' => 'image/webp',
                    'byte_size' => $fallback['byte_size'],
                    'checksum_sha256' => $fallback['checksum'],
                    'alt_text' => trim($altText),
                    'licence' => $licence,
                    'safe_metadata' => [
                        'gps_removed' => true,
                        'orientation_normalized' => true,
                        'width' => $fallback['width'],
                        'height' => $fallback['height'],
                    ],
                    'retained_until' => now()->addDays(
                        $this->config->integer('images.place_uploads.pending_retention_days'),
                    ),
                ]);
                $media = PlaceMedia::query()->create([
                    'media_key' => $mediaKey,
                    'place_id' => $lockedPlace->id,
                    'content_media_asset_id' => $asset->id,
                    'attached_by_user_id' => $actor->id,
                    'status' => PlaceMediaStatus::PendingReview,
                    'position' => $count + 1,
                    'is_featured' => false,
                    'attribution' => trim($attribution),
                    'licence' => $licence,
                    'upload_key' => $uploadKey,
                    'retained_until' => now()->addDays(
                        $this->config->integer('images.place_uploads.pending_retention_days'),
                    ),
                ]);

                foreach ($storedVariants as $variant => $stored) {
                    PlaceMediaVariantModel::query()->create([
                        'place_media_id' => $media->id,
                        'variant' => PlaceMediaVariant::from($variant),
                        'status' => PlaceMediaVariantStatus::Ready,
                        'disk' => 'local',
                        'path' => $stored['path'],
                        'mime_type' => 'image/webp',
                        'width' => $stored['width'],
                        'height' => $stored['height'],
                        'byte_size' => $stored['byte_size'],
                        'checksum_sha256' => $stored['checksum'],
                        'generated_at' => now(),
                    ]);
                }

                PlaceMediaEvent::query()->create([
                    'place_media_id' => $media->id,
                    'actor_user_id' => $actor->id,
                    'event_type' => 'uploaded',
                    'idempotency_key' => hash('sha256', "place-media-upload-event|{$uploadKey}"),
                    'metadata' => ['status' => PlaceMediaStatus::PendingReview->value],
                    'created_at' => now(),
                ]);

                return $media->load(['asset', 'variants']);
            }, 3);
        } catch (Throwable $exception) {
            Storage::disk('local')->deleteDirectory($directory);

            throw $exception;
        }
    }

    /** @return array<string, array{path: string, width: int, height: int, byte_size: int, checksum: string}> */
    private function storeVariants(UploadedFile $upload, string $directory): array
    {
        $stored = [];

        try {
            foreach ($this->config->array('images.place_uploads.variants') as $variant => $dimensions) {
                $path = $this->images
                    ->fromUpload($upload)
                    ->orient()
                    ->scale(width: (int) $dimensions['width'], height: (int) $dimensions['height'])
                    ->optimize(
                        format: $this->config->string('images.place_uploads.format'),
                        quality: $this->config->integer('images.place_uploads.quality'),
                    )
                    ->store(path: $directory, disk: 'local');

                if (! is_string($path)) {
                    $this->fail();
                }

                $absolutePath = Storage::disk('local')->path($path);
                $imageSize = @getimagesize($absolutePath);
                $byteSize = @filesize($absolutePath);
                $checksum = @hash_file('sha256', $absolutePath);

                if ($imageSize === false || ! is_int($byteSize) || ! is_string($checksum)) {
                    $this->fail();
                }

                $stored[(string) $variant] = [
                    'path' => $path,
                    'width' => (int) $imageSize[0],
                    'height' => (int) $imageSize[1],
                    'byte_size' => $byteSize,
                    'checksum' => $checksum,
                ];
            }
        } catch (ImageException) {
            Storage::disk('local')->deleteDirectory($directory);
            $this->fail();
        }

        return $stored;
    }

    private function originalName(UploadedFile $upload): string
    {
        $name = basename(str_replace('\\', '/', $upload->getClientOriginalName()));

        return Str::limit($name !== '' ? $name : 'place-image', 255, '');
    }

    private function validateReplay(PlaceMedia $media, Place $place, User $actor): PlaceMedia
    {
        if ($media->place_id !== $place->id || $media->attached_by_user_id !== $actor->id) {
            throw ValidationException::withMessages([
                'idempotency_key' => __('places.validation.idempotency_conflict'),
            ]);
        }

        return $media;
    }

    private function fail(): never
    {
        throw ValidationException::withMessages([
            'upload' => __('places.media.validation.processing'),
        ]);
    }
}
