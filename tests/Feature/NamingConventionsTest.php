<?php

use Illuminate\Support\Facades\File;

test('first party technical names stay free of product prefixes', function () {
    $sourceRoots = collect([
        app_path(),
        base_path('bootstrap'),
        config_path(),
        database_path(),
        resource_path(),
        base_path('routes'),
        base_path('tests'),
    ])->filter(fn (string $path): bool => File::isDirectory($path));

    $legacyNames = [
        'first' => [
            'studly' => implode('', ['Pet', 'Social']),
            'camel' => implode('', ['pet', 'Social']),
            'compact' => implode('', ['pet', 'social']),
            'kebab' => implode('-', ['pet', 'social']),
            'snake' => implode('_', ['pet', 'social']),
        ],
        'second' => [
            'studly' => implode('', ['Paw', 'Circle']),
            'camel' => implode('', ['paw', 'Circle']),
            'compact' => implode('', ['paw', 'circle']),
            'kebab' => implode('-', ['paw', 'circle']),
            'snake' => implode('_', ['paw', 'circle']),
        ],
    ];

    $pathTokens = collect($legacyNames)
        ->flatMap(fn (array $formats): array => array_values($formats))
        ->map(fn (string $token): string => strtolower($token))
        ->unique()
        ->values();

    $pathViolations = $sourceRoots
        ->flatMap(fn (string $root) => File::allFiles($root))
        ->map(fn ($file): string => str_replace(
            DIRECTORY_SEPARATOR,
            '/',
            $file->getRelativePathname(),
        ))
        ->filter(fn (string $path): bool => $pathTokens->contains(
            fn (string $token): bool => str_contains(strtolower($path), $token),
        ))
        ->values()
        ->all();

    $first = $legacyNames['first'];
    $second = $legacyNames['second'];
    $patterns = [
        '/\b'.preg_quote($first['studly'], '/').'\w*\b/',
        '/\b'.preg_quote($first['camel'], '/').'\w*\b/',
        '/\b'.preg_quote($first['compact'], '/').'(?:[-_:]|\w)*\b/',
        '/\b'.preg_quote($first['kebab'], '/').'(?:[-_:.\w])*\b/i',
        '/\b'.preg_quote($first['snake'], '/').'(?:[-_:.\w])*\b/i',
        '/\b'.preg_quote($second['studly'], '/').'[A-Z0-9_]\w*\b/',
        '/\b'.preg_quote($second['camel'], '/').'\w*\b/',
        '/\b'.preg_quote($second['kebab'], '/').'(?:[-_:.\w])*\b/i',
        '/\b'.preg_quote($second['snake'], '/').'(?:[-_:.\w])*\b/i',
        '/\b'.preg_quote($second['compact'], '/').'[-_:]\S*/',
        '/\b(?:class|interface|trait|enum)\s+'.preg_quote($second['studly'], '/').'\b/',
    ];

    $contentViolations = $sourceRoots
        ->flatMap(fn (string $root) => File::allFiles($root))
        ->reject(fn ($file): bool => str_starts_with(
            str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname()),
            'cache/',
        ))
        ->filter(fn ($file): bool => in_array($file->getExtension(), [
            'css',
            'js',
            'json',
            'php',
            'ts',
            'xml',
            'yaml',
            'yml',
        ], true))
        ->flatMap(function ($file) use ($patterns): array {
            $contents = File::get($file->getPathname());
            $relativePath = str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                str($file->getPathname())->after(base_path().DIRECTORY_SEPARATOR)->toString(),
            );

            return collect($patterns)
                ->filter(fn (string $pattern): bool => preg_match($pattern, $contents) === 1)
                ->map(fn (string $pattern): string => $relativePath.' matches '.$pattern)
                ->values()
                ->all();
        })
        ->values()
        ->all();

    expect($pathViolations)->toBeEmpty(
        'Technical product prefixes found in paths: '.implode(', ', $pathViolations),
    );

    expect($contentViolations)->toBeEmpty(
        'Technical product prefixes found in source: '.implode(', ', $contentViolations),
    );
});
