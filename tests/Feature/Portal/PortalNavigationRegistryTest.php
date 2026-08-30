<?php

declare(strict_types=1);

use App\Services\PortalNavigationRegistry;
use Illuminate\Support\Facades\Route;

test('desktop and mobile navigation derive from one typed registry', function (): void {
    $registry = app(PortalNavigationRegistry::class);
    $destinations = $registry->destinations($this->authenticatedUser);
    $desktop = $registry->forChannel('desktop', $this->authenticatedUser);
    $mobile = $registry->forChannel('mobile', $this->authenticatedUser);

    expect(array_column($desktop, 'key'))->toBe(array_values(array_map(
        static fn (array $destination): string => $destination['key'],
        array_filter($destinations, static fn (array $destination): bool => $destination['desktop']),
    )))->and(array_column($mobile, 'key'))->toBe(array_values(array_map(
        static fn (array $destination): string => $destination['key'],
        array_filter($destinations, static fn (array $destination): bool => $destination['mobile']),
    )));

    foreach ($destinations as $destination) {
        expect(Route::has($destination['route']), $destination['key'])->toBeTrue();
    }
});

test('page descriptors provide canonical active state breadcrumbs actions and safe back destinations', function (): void {
    $registry = app(PortalNavigationRegistry::class);
    $descriptor = $registry->page('profile.settings', $this->authenticatedUser);

    expect($descriptor)->toMatchArray([
        'module' => 'settings',
        'active' => 'settings',
        'canonical_url' => route('profile.settings'),
    ])->and($descriptor['breadcrumbs'])->not->toBeEmpty()
        ->and($descriptor['back']['url'])->toBe(route('portal.dashboard'));
});

test('shared navigation presentation is localized across en lt and ru', function (string $locale): void {
    app()->setLocale($locale);
    $registry = app(PortalNavigationRegistry::class);
    $items = $registry->forChannel('desktop', $this->authenticatedUser);

    expect($items)->not->toBeEmpty();

    foreach ($items as $item) {
        expect($item['label'])->not->toBe('')
            ->and($item['label'])->not->toStartWith('portal.');
    }
})->with(['en', 'lt', 'ru']);
