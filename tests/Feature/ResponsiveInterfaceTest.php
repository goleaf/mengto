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
