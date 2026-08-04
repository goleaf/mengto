<?php

declare(strict_types=1);

use App\Services\PlaceState;
use Database\Seeders\PlaceDemoSeeder;
use Illuminate\Support\Arr;

test('the place directory owns a complete localized system contract', function (): void {
    $english = Arr::dot(require lang_path('en/place_directory.php'));

    expect($english)
        ->toHaveCount(188)
        ->toHaveKey('page.action')
        ->toHaveKey('summary.items.open.label')
        ->toHaveKey('search.placeholder')
        ->toHaveKey('options.categories.park')
        ->toHaveKey('options.modes.favorites')
        ->toHaveKey('map.controls')
        ->toHaveKey('comparison.title');

    foreach (['lt', 'ru'] as $locale) {
        $localized = Arr::dot(require lang_path("{$locale}/place_directory.php"));

        expect(array_keys($localized), $locale)->toBe(array_keys($english));

        foreach ($english as $key => $value) {
            expect($localized[$key], "{$locale}:{$key}")->not->toBe($value);
        }
    }
});

test('the place directory renders localized system copy', function (string $locale): void {
    $this->seed(PlaceDemoSeeder::class);
    $this->authenticatedUser->update(['locale' => $locale]);

    $response = $this->get(route('places.index'))->assertOk();

    foreach ([
        'page.action',
        'summary.items.open.label',
        'search.placeholder',
        'options.categories.park',
        'options.modes.favorites',
        'map.controls',
        'comparison.title',
    ] as $key) {
        $response->assertSee(trans("place_directory.{$key}", locale: $locale));
    }

    $this->get(route('places.index', ['emergency' => 1]))
        ->assertOk()
        ->assertSee(trans('place_directory.page.emergency_title', locale: $locale));
})->with(['lt', 'ru']);

test('place directory controls keep stable codes and canonical icons', function (): void {
    $this->seed(PlaceDemoSeeder::class);

    $xpath = responseXPath($this->get(route('places.index'))->assertOk());

    $categoryIcons = [
        'all' => 'layout-grid',
        'park' => 'trees',
        'dog-park' => 'fence',
        'route' => 'route',
        'vet' => 'stethoscope',
        'emergency-vet' => 'siren',
        'pet-store' => 'shopping-bag',
        'grooming' => 'scissors',
        'shelter' => 'house-heart',
        'pet-cafe' => 'coffee',
    ];
    $modeIcons = [
        'browse' => 'layout-grid',
        'favorites' => 'bookmark',
        'visited' => 'map-pin-check',
        'events' => 'calendar-days',
        'warnings' => 'triangle-alert',
        'emergency' => 'siren',
    ];
    $viewIcons = [
        'split' => 'panel-left',
        'map' => 'map',
        'list' => 'list',
        'fullscreen' => 'maximize-2',
        'route' => 'route',
    ];
    $layerIcons = [
        'places' => 'map-pinned',
        'routes' => 'route',
        'events' => 'calendar-days',
        'warnings' => 'triangle-alert',
        'lost-pets' => 'scan-search',
        'emergency' => 'siren',
    ];

    expect($xpath->query('//*[@data-place-categories]/button')->length)->toBe(count($categoryIcons))
        ->and($xpath->query("//*[@data-place-controls]//nav[contains(@class, 'place-directory__modes')]/a")->length)->toBe(count($modeIcons))
        ->and($xpath->query("//*[@data-place-controls]//nav[contains(@class, 'place-directory__views')]/a")->length)->toBe(count($viewIcons))
        ->and($xpath->query('//*[@data-place-layers]/a')->length)->toBe(count($layerIcons));

    foreach ($categoryIcons as $value => $icon) {
        expect($xpath->query("//*[@data-place-categories]/button[@name='category' and @value='{$value}']//*[@data-ui-icon='{$icon}']")->length, $value)->toBe(1);
    }

    foreach (['mode' => $modeIcons, 'view' => $viewIcons] as $parameter => $icons) {
        foreach ($icons as $value => $icon) {
            expect($xpath->query("//*[@data-place-controls]//a[contains(@href, '{$parameter}={$value}')]//*[@data-ui-icon='{$icon}']")->length, "{$parameter}:{$value}")->toBe(1);
        }
    }

    foreach ($layerIcons as $value => $icon) {
        expect($xpath->query("//*[@data-place-layers]/a[contains(@href, 'layer={$value}')]//*[@data-ui-icon='{$icon}']")->length, "layer:{$value}")->toBe(1);
    }
});

test('generalized location labels resolve in the current locale', function (): void {
    $state = app(PlaceState::class);

    app()->setLocale('en');
    expect($state->generalizedLocation()['label'])->toBe('Vilnius selected manually');

    $state->setGeneralizedLocation(54.6872, 25.2797);
    app()->setLocale('ru');
    expect($state->generalizedLocation()['label'])->toBe('Примерная текущая область');

    $state->clearGeneralizedLocation();
    app()->setLocale('lt');
    expect($state->generalizedLocation()['label'])->toBe('Vilnius pasirinktas rankiniu būdu');
});
