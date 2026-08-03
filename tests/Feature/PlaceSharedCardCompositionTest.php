<?php

use Database\Seeders\PlaceDemoSeeder;

beforeEach(function () {
    $this->seed(PlaceDemoSeeder::class);
});

test('place directory composes shared card leaves without replacing its map aware shell', function () {
    $response = $this->get(route('places.index', ['view' => 'list']));
    $xpath = responseXPath($response);
    $cards = $xpath->query('//*[@data-place-card]');

    $response->assertSuccessful();

    expect($cards->length)->toBe(6);

    foreach ($cards as $card) {
        $media = $xpath->query('.//*[@data-ui-card-media]', $card);
        $headings = $xpath->query('.//*[@data-card-heading]', $card);
        $descriptions = $xpath->query('.//*[@data-card-description]', $card);
        $mediaLinks = $xpath->query('.//*[@data-ui-card-media][self::a]', $card);
        $headingLinks = $xpath->query('.//*[@data-card-heading]//a', $card);

        expect($media->length)->toBe(1)
            ->and($headings->length)->toBe(1)
            ->and($descriptions->length)->toBe(1)
            ->and($mediaLinks->length)->toBe(1)
            ->and($headingLinks->length)->toBe(1)
            ->and($mediaLinks->item(0)?->attributes?->getNamedItem('href')?->nodeValue)
            ->toBe($headingLinks->item(0)?->attributes?->getNamedItem('href')?->nodeValue);
    }
});

test('place card uses semantic shared hooks while the detail dashboard stays domain specific', function () {
    $card = file_get_contents(resource_path('views/components/place-card.blade.php'));
    $dashboard = file_get_contents(resource_path('views/components/place-dashboard.blade.php'));
    $mapScript = file_get_contents(resource_path('js/places-map.js'));

    expect($card)
        ->not->toBeFalse()
        ->toContain('<x-card-media')
        ->toContain('<x-card-heading')
        ->toContain('<x-card-description')
        ->not->toContain('<x-directory-card')
        ->and($dashboard)
        ->not->toBeFalse()
        ->toContain('class="place-dashboard"')
        ->not->toContain('<x-directory-card')
        ->and($mapScript)
        ->not->toBeFalse()
        ->toContain("card.querySelector('[data-card-heading]')")
        ->not->toContain("card.querySelector('.place-card__title')");
});
