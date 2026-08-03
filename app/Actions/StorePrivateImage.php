<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Config\Repository;
use Illuminate\Http\UploadedFile;
use Illuminate\Image\ImageException;
use Illuminate\Image\ImageManager;
use Illuminate\Validation\ValidationException;

final readonly class StorePrivateImage
{
    public function __construct(
        private ImageManager $images,
        private Repository $config,
    ) {}

    public function handle(
        UploadedFile $upload,
        string $directory,
        string $validationKey = 'photo',
    ): string {
        $normalizedDirectory = trim(str_replace('\\', '/', $directory), '/');

        if ($normalizedDirectory === '' || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $normalizedDirectory) === 1) {
            $this->fail($validationKey);
        }

        try {
            $path = $this->images
                ->fromUpload($upload)
                ->orient()
                ->scale(
                    width: $this->config->integer('images.pet_profile_uploads.max_width'),
                    height: $this->config->integer('images.pet_profile_uploads.max_height'),
                )
                ->optimize(
                    format: $this->config->string('images.pet_profile_uploads.format'),
                    quality: $this->config->integer('images.pet_profile_uploads.quality'),
                )
                ->store(path: $normalizedDirectory, disk: 'local');
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
            $validationKey => __('pet_profiles.validation.photo_processing'),
        ]);
    }
}
