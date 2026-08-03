<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

final class PortalMediaResponse
{
    /** @var array<string, string> */
    private const array CONTENT_TYPES = [
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'mov' => 'video/quicktime',
        'mp4' => 'video/mp4',
        'png' => 'image/png',
        'vtt' => 'text/vtt; charset=UTF-8',
        'webm' => 'video/webm',
        'webp' => 'image/webp',
    ];

    /** @var list<string> */
    private const array ALLOWED_DIRECTORIES = [
        'forum/images',
        'forum/videos',
        'forum/captions',
        'lost-found/cases',
        'lost-found/sightings/photos',
        'lost-found/sightings/videos',
        'marketplace/listings',
        'marketplace/listing-videos',
    ];

    public function inline(string $path): StreamedResponse
    {
        $normalizedPath = $this->normalizeRelativePath($path);
        $allowedDirectory = $normalizedPath === null
            ? null
            : $this->allowedDirectory($normalizedPath);
        $contentType = $normalizedPath === null
            ? null
            : self::CONTENT_TYPES[strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION))] ?? null;

        if ($normalizedPath === null || $allowedDirectory === null || $contentType === null) {
            abort(404);
        }

        $filesystem = Storage::disk('public');

        if (! $filesystem instanceof FilesystemAdapter) {
            abort(404);
        }

        try {
            $diskRoot = realpath($filesystem->path(''));
            $directoryPath = realpath($filesystem->path($allowedDirectory));
            $filePath = realpath($filesystem->path($normalizedPath));
        } catch (Throwable) {
            abort(404);
        }

        if (
            ! is_string($diskRoot)
            || ! is_string($directoryPath)
            || ! is_string($filePath)
            || ! is_file($filePath)
            || ! $this->isWithin($directoryPath, $diskRoot)
            || ! $this->isWithin($filePath, $directoryPath)
        ) {
            abort(404);
        }

        return $filesystem->response(
            $normalizedPath,
            null,
            [
                'Cache-Control' => 'private, no-store',
                'Content-Type' => $contentType,
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }

    private function allowedDirectory(string $path): ?string
    {
        foreach (self::ALLOWED_DIRECTORIES as $directory) {
            if (str_starts_with($path, $directory.'/')) {
                return $directory;
            }
        }

        return null;
    }

    private function normalizeRelativePath(string $path): ?string
    {
        $normalized = str_replace('\\', '/', trim($path));

        if (
            $normalized === ''
            || str_starts_with($normalized, '/')
            || str_contains($normalized, "\0")
            || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $normalized) === 1
        ) {
            return null;
        }

        return trim($normalized, '/');
    }

    private function isWithin(string $path, string $directory): bool
    {
        return str_starts_with($path, rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
    }
}
