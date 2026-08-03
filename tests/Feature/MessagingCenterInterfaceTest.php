<?php

declare(strict_types=1);

test('message folders render in a dedicated toolbar before the messaging shell and remain navigable', function () {
    $response = $this->get(route('messages.index'))->assertOk();
    $xpath = responseXPath($response);

    $folderLinks = $xpath->query(
        '//nav[contains(concat(" ", normalize-space(@class), " "), " messaging-folders ")]'
        .'//div[contains(concat(" ", normalize-space(@class), " "), " messaging-folders__list ")]/a',
    );
    $toolbarBeforeShell = $xpath->query(
        '//div[contains(concat(" ", normalize-space(@class), " "), " messaging-page ")]'
        .'/nav[contains(concat(" ", normalize-space(@class), " "), " messaging-folders ")]'
        .'[following-sibling::div[contains(concat(" ", normalize-space(@class), " "), " messaging-shell ")]]',
    );
    $foldersInsideInbox = $xpath->query(
        '//aside[contains(concat(" ", normalize-space(@class), " "), " messaging-inbox ")]'
        .'//nav[contains(concat(" ", normalize-space(@class), " "), " messaging-folders ")]',
    );

    expect($folderLinks->length)->toBe(9)
        ->and($toolbarBeforeShell->length)->toBe(1)
        ->and($foldersInsideInbox->length)->toBe(0);

    foreach (['all', 'unread', 'friends', 'groups', 'events', 'specialists', 'family', 'requests', 'archived'] as $filter) {
        $this->get(route('messages.index', ['filter' => $filter]))->assertOk();
    }
});

test('message folders use a visible responsive grid while the inbox scrolls only conversations', function () {
    $styles = file_get_contents(resource_path('scss/_messaging.scss'));

    expect($styles)->toBeString()
        ->and($styles)->toContain('grid-template-columns: repeat(3, minmax(0, 1fr));')
        ->and($styles)->toContain('grid-template-columns: repeat(5, minmax(0, 1fr));')
        ->and($styles)->toContain('grid-template-columns: repeat(9, minmax(0, 1fr));')
        ->and($styles)->toContain('grid-template-rows: auto auto minmax(0, 1fr);')
        ->and($styles)->toContain('overscroll-behavior: contain;');
});
