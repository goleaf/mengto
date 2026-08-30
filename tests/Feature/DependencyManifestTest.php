<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('dependency installation is locked to the authorized toolchain and registry', function () {
    $composer = json_decode(File::get(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $lock = json_decode(File::get(base_path('package-lock.json')), true, flags: JSON_THROW_ON_ERROR);
    $npmConfig = File::get(base_path('.npmrc'));
    $resolvedHosts = collect($lock['packages'])
        ->pluck('resolved')
        ->filter()
        ->map(static fn (string $url): ?string => parse_url($url, PHP_URL_HOST))
        ->unique()
        ->values()
        ->all();

    expect($composer['scripts']['setup'])
        ->toContain('npm ci --ignore-scripts')
        ->toContain('@php artisan migrate')
        ->not->toContain(
            'npm install --ignore-scripts',
            '@php artisan migrate --force',
        )
        ->and($composer['config']['allow-plugins'])
        ->toBe(['pestphp/pest-plugin' => true])
        ->and($package['packageManager'])
        ->toBe('npm@12.0.2')
        ->and($package['engines'])
        ->toBe([
            'node' => '>=22.12.0 <27.0.0',
            'npm' => '>=12.0.2 <13.0.0',
        ])
        ->and(File::exists(base_path('.node-version'))
            ? trim(File::get(base_path('.node-version')))
            : null)
        ->toBe('26.4.0')
        ->and($npmConfig)
        ->toContain(
            'ignore-scripts=true',
            'engine-strict=true',
            'registry=https://registry.npmjs.org/',
            'strict-ssl=true',
        )
        ->and($resolvedHosts)
        ->toBe(['registry.npmjs.org']);
});

test('frontend fonts are resolved from the npm lock instead of the public internet', function () {
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $vite = File::get(base_path('vite.config.js'));

    expect($package['devDependencies'])
        ->toHaveKey('@fontsource/instrument-sans', '^5.3.0')
        ->and($vite)
        ->toContain(
            "import { fontsource } from 'laravel-vite-plugin/fonts';",
            "fontsource('Instrument Sans'",
            "package: '@fontsource/instrument-sans'",
        )
        ->not->toContain(
            "import { google } from 'laravel-vite-plugin/fonts';",
            "google('Instrument Sans'",
        );
});

test('livewire generators preserve class based components with separate views', function () {
    expect(config('livewire.make_command'))
        ->toBe([
            'type' => 'class',
            'emoji' => false,
            'with' => [
                'js' => false,
                'css' => false,
                'test' => false,
            ],
        ])
        ->and(config('livewire.class_namespace'))
        ->toBe('App\\Livewire')
        ->and(config('livewire.class_path'))
        ->toBe(app_path('Livewire'))
        ->and(config('livewire.view_path'))
        ->toBe(resource_path('views/livewire'));
});
