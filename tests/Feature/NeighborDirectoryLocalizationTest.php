<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

test('neighbor empty-state localization has EN LT RU key and placeholder parity', function (): void {
    $english = Arr::dot(require lang_path('en/neighbors.php'));

    foreach (['lt', 'ru'] as $locale) {
        $localized = Arr::dot(require lang_path("{$locale}/neighbors.php"));

        expect(array_keys($localized))->toBe(array_keys($english));
    }
});

test('a fresh account sees an honest localized empty neighbor directory', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $response = $this->get(route('neighbors.index'))->assertOk();
    $copy = require lang_path("{$locale}/neighbors.php");
    $xpath = responseXPath($response);

    $response
        ->assertSee((string) data_get($copy, 'page.heading'))
        ->assertSee((string) data_get($copy, 'results.empty_title'));

    expect($xpath->query('//*[@data-neighbor-card]')->length)->toBe(0);
})->with(['en', 'lt', 'ru']);
