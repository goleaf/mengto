<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('primary navigation renders every destination in the authenticated users locale', function (
    string $locale,
): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $response = $this->get(route('preview.feed'))->assertOk();
    $xpath = responseXPath($response);
    $items = trans('navigation.items', locale: $locale);
    $desktopLabels = array_values(array_map(
        static fn (array $item): string => $item['label'],
        $items,
    ));
    $mobileLabels = array_slice(array_values(array_map(
        static fn (array $item): string => $item['mobile_label'],
        $items,
    )), 0, 11);
    $renderedDesktopLabels = array_map(
        static fn (DOMNode $node): string => trim((string) $node->textContent),
        iterator_to_array($xpath->query(
            '//nav[@data-navigation-variant="desktop"]//a[@data-nav-item]/span',
        )),
    );
    $renderedMobileLabels = array_map(
        static fn (DOMNode $node): string => trim((string) $node->textContent),
        iterator_to_array($xpath->query(
            '//nav[@data-navigation-variant="mobile"]//a[@data-nav-item]/span',
        )),
    );

    expect($renderedDesktopLabels)
        ->toBe($desktopLabels)
        ->and($renderedMobileLabels)->toBe($mobileLabels)
        ->and($xpath->evaluate('string(//nav[@data-navigation-variant="desktop"]/@aria-label)'))
        ->toBe(trans('navigation.primary_label', locale: $locale))
        ->and($xpath->evaluate('string(//nav[@data-navigation-variant="mobile"]/@aria-label)'))
        ->toBe(trans('navigation.mobile_label', locale: $locale));
})->with(['en', 'lt', 'ru']);

test('non english navigation copy is complete and does not fall back to english', function (): void {
    $english = trans('navigation', locale: 'en');

    foreach (['lt', 'ru'] as $locale) {
        $localized = trans('navigation', locale: $locale);

        expect($localized['primary_label'])->not->toBe($english['primary_label'])
            ->and($localized['mobile_label'])->not->toBe($english['mobile_label'])
            ->and($localized['unavailable'])->not->toBe($english['unavailable']);

        foreach ($english['items'] as $name => $item) {
            expect($localized['items'][$name]['label'], "{$locale}.{$name}.label")
                ->not->toBe($item['label'])
                ->and($localized['items'][$name]['mobile_label'], "{$locale}.{$name}.mobile_label")
                ->not->toBe($item['mobile_label']);
        }
    }
});

test('navigation labels are prepared outside blade and covered by the browser matrix', function (): void {
    $component = File::get(app_path('View/Components/PrimaryNavigation.php'));
    $view = File::get(resource_path('views/components/primary-navigation.blade.php'));
    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));

    expect($component)
        ->toContain('__("navigation.items.')
        ->not->toContain("'label' => 'Feed'", "'mobile_label' => 'Feed'")
        ->and($view)
        ->toContain("__('navigation.mobile_label')", "__('navigation.primary_label')")
        ->and($browser)
        ->toContain(
            'englishNavigationCopy',
            'behavior.navigationCopy.desktopItems.length === 13',
            'English global navigation fallback remains.',
        );
});
