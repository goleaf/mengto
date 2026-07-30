<?php

use Illuminate\Support\Facades\File;

test('anonymous Blade components stay flat and prefix free', function () {
    $componentRoot = resource_path('views/components');

    $nestedComponents = collect(File::allFiles($componentRoot))
        ->map(fn ($file): string => $file->getRelativePathname())
        ->filter(fn (string $path): bool => str_contains($path, DIRECTORY_SEPARATOR))
        ->values()
        ->all();

    $prefixedTags = collect(File::allFiles(resource_path('views')))
        ->flatMap(function ($file): array {
            preg_match_all(
                '/<\/?x-[a-z0-9_-]+\.[a-z0-9_.-]+/i',
                File::get($file->getPathname()),
                $matches,
            );

            $view = $file->getRelativePathname();

            return array_map(
                fn (string $tag): string => $view.': '.$tag,
                $matches[0],
            );
        })
        ->values()
        ->all();

    expect($nestedComponents)
        ->toBeEmpty()
        ->and($prefixedTags)
        ->toBeEmpty();
});
