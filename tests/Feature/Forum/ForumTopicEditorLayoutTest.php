<?php

declare(strict_types=1);

test('topic editor unifies publishing guidance above the complete form', function () {
    $response = $this->get(route('forum.topics.create'))->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->query('//*[@data-forum-editor-shell]')->length)
        ->toBe(1)
        ->and($xpath->query(
            '//*[@data-forum-editor-shell]/*[@data-forum-publishing-guidance]'
            .'[following-sibling::form[@data-forum-editor]]',
        )->length)
        ->toBe(1)
        ->and($xpath->query(
            '//*[@data-forum-publishing-guidance]//ul/li',
        )->length)
        ->toBe(5)
        ->and($xpath->query(
            '//main//*[contains(concat(" ", normalize-space(@class), " "), " forum-sidebar ")]',
        )->length)
        ->toBe(0)
        ->and($xpath->query(
            '//form[@data-forum-editor]/section[@data-forum-editor-section]',
        )->length)
        ->toBe(3);
});

test('redesigned topic editor retains every authoring field and submission intent', function () {
    $response = $this->get(route('forum.topics.create'))->assertOk();
    $xpath = responseXPath($response);

    foreach ([
        'type',
        'category',
        'subcategory',
        'pet_key',
        'title',
        'body',
        'tried',
        'desired_answer',
        'location',
        'tags',
        'visibility',
        'comment_policy',
        'language',
        'veterinary_status',
        'photos[]',
        'video',
        'photo_alt',
        'video_transcript',
        'video_captions',
        'video_caption_locale',
        'is_medical',
        'is_urgent',
        'sensitive_media',
    ] as $fieldName) {
        expect($xpath->query(
            sprintf('//form[@data-forum-editor]//*[@name="%s"]', $fieldName),
        )->length, $fieldName)->toBe(1);
    }

    expect($xpath->query(
        '//form[@data-forum-editor]//button[@type="submit"][@name="intent"][@value="draft"]',
    )->length)
        ->toBe(1)
        ->and($xpath->query(
            '//form[@data-forum-editor]//button[@type="submit"][@name="intent"][@value="publish"]',
        )->length)
        ->toBe(1)
        ->and($xpath->query(
            '//form[@data-forum-editor]//*[@name="animal_context" or @name="taxon_ids[]"]',
        )->length)
        ->toBeGreaterThan(0);
});
