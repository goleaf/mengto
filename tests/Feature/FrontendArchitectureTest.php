<?php

declare(strict_types=1);

use IlluminateSupportFacadesFile;

test('blade templates prohibit executable application boundaries', function () {
    $patterns = [
        '/@php\b|@endphp\b|<\?php/',
        '/\{!!/',
        '/\b(?:app|resolve|collect)\s*\(/',
        '/\b(?:DB|Cache|Gate|Auth|Config|Route|View|Storage|Log)::/',
        '/\bApp\\\\(?:Models|Services|Actions|Facades)\\\\/',
        '/\b[A-Z][A-Za-z0-9_\\\\]*::[A-Za-z_][A-Za-z0-9_]*\s*\(/',
        '/<script\b/i',
    ];

    foreach (File::allFiles(resource_path('views')) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        foreach ($patterns as $pattern) {
            expect(
                preg_match($pattern, $file->getContents()),
                $file->getRelativePathname().' matched '.$pattern,
            )->toBe(0);
        }
    }
});

test('frontend manifests and source do not install a second alpine instance', function () {
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $composer = json_decode(File::get(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);
    $frontendPackages = array_keys([
        ...($package['dependencies'] ?? []),
        ...($package['devDependencies'] ?? []),
    ]);
    $phpPackages = array_keys([
        ...($composer['require'] ?? []),
        ...($composer['require-dev'] ?? []),
    ]);

    expect($frontendPackages)
        ->not->toContain('alpinejs')
        ->and(array_filter(
            $frontendPackages,
            static fn (string $package): bool => str_starts_with($package, '@alpinejs/'),
        ))->toBeEmpty()
        ->and($phpPackages)->not->toContain('livewire/flux')
        ->and($phpPackages)->not->toContain('livewire/flux-pro');

    foreach (File::allFiles(resource_path('js')) as $file) {
        if ($file->getExtension() !== 'js') {
            continue;
        }

        expect($file->getContents(), $file->getRelativePathname())
            ->not->toMatch('/(?:from|require\s*\()\s*[\'\"](?:@alpinejs\/[^\'\"]+|alpinejs)[\'\"]/')
            ->not->toMatch('/\bAlpine\.start\s*\(/');
    }
});

test('page bound javascript uses the shared livewire navigation lifecycle', function () {
    $helper = resource_path('js/support/page-lifecycle.js');

    expect(File::exists($helper), $helper)->toBeTrue();

    if (! File::exists($helper)) {
        return;
    }

    expect(File::get($helper))
        ->toContain("document.addEventListener('livewire:navigating'")
        ->toContain("document.addEventListener('livewire:navigated'")
        ->toContain('cleanup?.()');

    foreach ([
        'action-forms.js',
        'forum.js',
        'messaging-center.js',
        'places-map.js',
    ] as $module) {
        expect(File::get(resource_path("js/{$module}")), $module)
            ->toContain("from './support/page-lifecycle'")
            ->toContain('registerPageLifecycle(');
    }
});

test('custom javascript receives complete user facing sentences from localized data', function () {
    foreach (File::allFiles(resource_path('js')) as $file) {
        if ($file->getExtension() !== 'js') {
            continue;
        }

        expect($file->getContents(), $file->getRelativePathname())
            ->not->toMatch('/\.textContent\s*=\s*`[^`]*\pL[^`]*`/u')
            ->not->toMatch('/\.textContent\s*=\s*[\'\"][^\'\"]*\pL[^\'\"]*[\'\"]/u');
    }
});
