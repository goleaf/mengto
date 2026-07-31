<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

final class PrivateFileResponse
{
    /**
     * @param  array<string, string>  $headers
     */
    public function download(
        string $disk,
        string $path,
        string $allowedDirectory,
        string $downloadName,
        array $headers = [],
    ): StreamedResponse {
        $filesystem = $this->readableFilesystem($disk, $path, $allowedDirectory);

        return $filesystem->download($path, $downloadName, $headers);
    }

    /**
     * @param  array<string, string>  $headers
     */
    public function inline(
        string $disk,
        string $path,
        string $allowedDirectory,
        array $headers = [],
    ): StreamedResponse {
        $filesystem = $this->readableFilesystem($disk, $path, $allowedDirectory);

        return $filesystem->response($path, null, $headers, 'inline');
    }

    private function readableFilesystem(
        string $disk,
        string $path,
        string $allowedDirectory,
    ): FilesystemAdapter {
        if ($disk !== 'local') {
            abort(404);
        }

        $normalizedPath = $this->normalizeRelativePath($path);
        $normalizedDirectory = $this->normalizeRelativePath($allowedDirectory);

        if (
            $normalizedPath === null
            || $normalizedDirectory === null
            || ! str_starts_with($normalizedPath, $normalizedDirectory.'/')
        ) {
            abort(404);
        }

        $filesystem = Storage::disk($disk);

        if (! $filesystem instanceof FilesystemAdapter) {
            abort(404);
        }

        try {
            $diskRoot = realpath($filesystem->path(''));
            $directoryPath = realpath($filesystem->path($normalizedDirectory));
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

        return $filesystem;
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
