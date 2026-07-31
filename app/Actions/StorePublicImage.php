<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Config\Repository;
use Illuminate\Http\UploadedFile;
use Illuminate\Image\ImageException;
use Illuminate\Image\ImageManager;
use Illuminate\Validation\ValidationException;

final readonly class StorePublicImage
{
    public function __construct(
        private ImageManager $images,
        private Repository $config,
    ) {}

    public function handle(
        UploadedFile $upload,
        string $directory,
        string $validationKey = 'photos',
    ): string {
        try {
            $path = $this->images
                ->fromUpload($upload)
                ->orient()
                ->scale(
                    width: $this->config->integer('images.public_uploads.max_width'),
                    height: $this->config->integer('images.public_uploads.max_height'),
                )
                ->optimize(
                    format: $this->config->string('images.public_uploads.format'),
                    quality: $this->config->integer('images.public_uploads.quality'),
                )
                ->storePublicly(path: $directory, disk: 'public');
        } catch (ImageException) {
            $this->fail($validationKey);
        }

        if (! is_string($path)) {
            $this->fail($validationKey);
        }

        return $path;
    }

    private function fail(string $validationKey): never
    {
        throw ValidationException::withMessages([
            $validationKey => __('messages.public_image_processing_failed'),
        ]);
    }
}
