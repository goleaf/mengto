<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('tailwind uses an explicit css first source registry and built in variants', function (): void {
    $stylesheet = File::get(resource_path('css/app.css'));

    expect($stylesheet)
        ->toStartWith("@import 'tailwindcss' source(none);")
        ->toContain(
            "@source '../views/**/*.blade.php';",
            "@source '../js/**/*.js';",
            "@source '../../app/Livewire/**/*.php';",
            "@source '../../app/View/Components/**/*.php';",
            "@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/tailwind.blade.php';",
            "@source '../../vendor/livewire/livewire/src/Features/SupportPagination/views/tailwind.blade.php';",
            "@source '../../vendor/livewire/livewire/src/Features/SupportPagination/views/simple-tailwind.blade.php';",
        )
        ->not->toContain(
            'resources/views/*.blade.php',
            'SupportPagination/views/*.blade.php',
            '@custom-variant forced-colors',
            '@tailwind base',
            '@tailwind components',
            '@tailwind utilities',
        );

    foreach (['tailwind.config.js', 'tailwind.config.cjs', 'postcss.config.js', 'postcss.config.cjs'] as $obsoleteConfig) {
        expect(base_path($obsoleteConfig), $obsoleteConfig)->not->toBeFile();
    }
});

test('tailwind theme owns every cross asset design token', function (): void {
    $stylesheet = File::get(resource_path('css/app.css'));

    foreach ([
        '--color-paw-canvas:',
        '--color-paw-surface:',
        '--color-border-subtle:',
        '--color-border-strong:',
        '--color-control-border:',
        '--color-text-muted:',
        '--color-focus:',
        '--color-status-warning-foreground:',
        '--spacing-touch:',
        '--container-content:',
        '--container-reading:',
        '--tracking-eyebrow:',
        '--shadow-control:',
        '--transition-duration-interface:',
    ] as $token) {
        expect($stylesheet, $token)->toContain($token);
    }

    expect($stylesheet)->not->toContain('--duration-interface:');
});

test('tailwind utility names are never assembled from runtime fragments', function (): void {
    $roots = [
        app_path(),
        resource_path('views'),
        resource_path('js'),
    ];
    $extensions = ['php', 'blade.php', 'js'];
    $patterns = [
        '/(?:bg|text|border|ring|outline|shadow|grid-cols|col-span|row-span|from|via|to)-\$\{/',
        '/[\'\"](?:bg|text|border|ring|outline|shadow|grid-cols|col-span|row-span|from|via|to)-[\'\"]\s*\./',
        '/\"(?:bg|text|border|ring|outline|shadow|grid-cols|col-span|row-span|from|via|to)-\{\$/',
    ];
    $violations = [];

    foreach ($roots as $root) {
        foreach (File::allFiles($root) as $file) {
            $relativePath = $file->getRelativePathname();

            if (! collect($extensions)->contains(
                static fn (string $extension): bool => str_ends_with($relativePath, $extension),
            )) {
                continue;
            }

            $contents = $file->getContents();

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $contents) === 1) {
                    $violations[] = $file->getPathname();
                }
            }
        }
    }

    expect(array_values(array_unique($violations)))->toBe([]);
});

test('the local brand font covers every supported latin locale', function (): void {
    $viteConfig = File::get(base_path('vite.config.js'));

    expect($viteConfig)
        ->toContain("fontsource('Instrument Sans'")
        ->toMatch("/subsets:\s*\[[^\]]*'latin'[^\]]*'latin-ext'[^\]]*\]/");
});
