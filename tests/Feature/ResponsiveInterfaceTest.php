<?php

declare(strict_types=1);

test('search directories render square accessible clear filter controls', function () {
    foreach (['lost-found.index', 'marketplace.index', 'experts.index'] as $routeName) {
        $response = $this->get(route($routeName))->assertOk();
        $xpath = responseXPath($response);

        expect(
            $xpath->query(
                '//form[@role="search"]//a[contains(concat(" ", normalize-space(@class), " "), " action--icon ")]',
            )->length,
            $routeName,
        )->toBe(1);
    }
});

test('mobile search and forum controls use the shared touch target token', function () {
    $formStyles = file_get_contents(resource_path('scss/_forms.scss'));
    $forumStyles = file_get_contents(resource_path('scss/_forum.scss'));

    expect($formStyles)->toBeString()
        ->and($formStyles)->toContain(".app-main form[role='search']")
        ->and($formStyles)->toContain('min-block-size: $touch-target;')
        ->and($forumStyles)->toBeString()
        ->and($forumStyles)->toContain('.forum-categories')
        ->and($forumStyles)->toContain('.forum-filter-tabs')
        ->and(substr_count((string) $forumStyles, 'min-block-size: $touch-target;'))->toBeGreaterThanOrEqual(2);

    $forum = $this->get(route('forum.index'))->assertOk();
    $xpath = responseXPath($forum);

    expect($xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " forum-categories ")]//a')->length)
        ->toBeGreaterThan(0)
        ->and($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " forum-filter-tabs ")]//a')->length)
        ->toBeGreaterThan(0);
});

test('forum accessibility styles preserve touch, focus, reflow, and contrast contracts', function () {
    $forumStyles = file_get_contents(resource_path('scss/_forum.scss'));
    $applicationStyles = file_get_contents(resource_path('css/app.css'));

    expect($forumStyles)->toBeString()
        ->and($forumStyles)->toContain('.forum-errors')
        ->and($forumStyles)->toContain('outline: 3px solid #24745d;')
        ->and($forumStyles)->toContain(".forum-page nav[role='navigation'] a")
        ->and($forumStyles)->toContain('min-block-size: $touch-target;')
        ->and($forumStyles)->toContain("[aria-invalid='true']")
        ->and($applicationStyles)->toBeString()
        ->and($applicationStyles)->toContain('@media (prefers-reduced-motion: reduce)')
        ->and($applicationStyles)->toContain('@media (forced-colors: active)')
        ->and($applicationStyles)->toContain('min-width: 20rem')
        ->and($applicationStyles)->toContain('overflow-x: clip');

    $relativeLuminance = static function (string $hex): float {
        $channels = array_map(
            static fn (int $offset): float => hexdec(substr($hex, $offset, 2)) / 255,
            [0, 2, 4],
        );
        $linear = array_map(
            static fn (float $value): float => $value <= 0.04045
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4,
            $channels,
        );

        return (0.2126 * $linear[0]) + (0.7152 * $linear[1]) + (0.0722 * $linear[2]);
    };
    $contrast = static function (string $foreground, string $background) use ($relativeLuminance): float {
        $first = $relativeLuminance($foreground);
        $second = $relativeLuminance($background);

        return (max($first, $second) + 0.05) / (min($first, $second) + 0.05);
    };

    foreach ([
        ['27312f', 'ffffff'],
        ['404b47', 'ffffff'],
        ['66706b', 'fbfaf6'],
        ['185b48', 'e2f3eb'],
        ['953629', 'f9e5e2'],
        ['745d0d', 'fff3c5'],
        ['8a3429', 'fff3f1'],
        ['24745d', 'ffffff'],
    ] as [$foreground, $background]) {
        expect($contrast($foreground, $background), "{$foreground} on {$background}")
            ->toBeGreaterThanOrEqual(4.5);
    }
});
