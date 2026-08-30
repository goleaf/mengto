<?php

declare(strict_types=1);

use App\Services\PlaceCatalog;
use App\Services\PlaceContentCatalog;
use Database\Seeders\PlaceDemoSeeder;
use Illuminate\Support\Str;

test('place catalog and gallery media use local responsive assets with stable dimensions', function () {
    $places = app(PlaceCatalog::class)->demoRecords();
    $contentCatalog = app(PlaceContentCatalog::class);
    $expectedDimensions = [
        'image' => [1200, 900],
        'image_medium' => [900, 675],
        'image_small' => [576, 432],
    ];
    $primaryImages = [];

    $assertLocalResponsiveSet = function (array $media) use ($expectedDimensions): void {
        foreach ($expectedDimensions as $key => $expected) {
            $url = $media[$key];
            $path = public_path(Str::after($url, '/'));
            $dimensions = is_file($path) ? getimagesize($path) : false;

            expect($url)
                ->toStartWith('/images/places/')
                ->not->toContain('://')
                ->not->toContain('?')
                ->and($dimensions)->not->toBeFalse()
                ->and(array_slice($dimensions, 0, 2))->toBe($expected);
        }
    };

    foreach ($places as $place) {
        $assertLocalResponsiveSet($place);
        $primaryImages[] = $place['image'];

        foreach ($contentCatalog->content($place)['gallery'] as $photo) {
            $assertLocalResponsiveSet($photo);
        }
    }

    $groomingPlace = collect($places)->firstWhere('primary_category', 'grooming');

    expect(array_unique($primaryImages))->toHaveCount(7)
        ->and($groomingPlace)->not->toBeNull()
        ->and($groomingPlace['image_alt'])->toBe(__('messages.quiet_pet_grooming_workspace_with_clean_equipment'));
});

test('place directory hero and gallery render truthful local srcsets', function () {
    $this->seed(PlaceDemoSeeder::class);

    $assertResponsiveImages = function ($images): void {
        foreach ($images as $image) {
            $source = (string) $image->attributes?->getNamedItem('src')?->nodeValue;
            $sourceSet = (string) $image->attributes?->getNamedItem('srcset')?->nodeValue;

            expect($source)
                ->toStartWith('/images/places/')
                ->toEndWith('-sm.jpg')
                ->and($sourceSet)
                ->toMatch('/-sm\.jpg 576w, \/images\/places\/.+-md\.jpg 900w, \/images\/places\/.+-lg\.jpg 1200w/')
                ->not->toContain('://');
        }
    };

    $directory = $this->get(route('places.index', ['view' => 'list']));
    $directoryImages = responseXPath($directory)->query('//*[@data-place-card]//img');

    $directory->assertSuccessful();
    expect($directoryImages->length)->toBe(6);
    $assertResponsiveImages($directoryImages);

    $detail = $this->get(route('places.show', [
        'place' => 'vingis-quiet-loop',
        'tab' => 'photos',
    ]));
    $detailImages = responseXPath($detail)->query(
        '//section[contains(concat(" ", normalize-space(@class), " "), " place-hero ")]//img'
        .' | //div[contains(concat(" ", normalize-space(@class), " "), " place-gallery ")]//img',
    );

    $detail->assertSuccessful();
    expect($detailImages->length)->toBe(4);
    $assertResponsiveImages($detailImages);
});

test('place presentation services contain no runtime public image host', function () {
    $catalog = file_get_contents(app_path('Services/PlaceCatalog.php'));
    $contentCatalog = file_get_contents(app_path('Services/PlaceContentCatalog.php'));
    $card = file_get_contents(resource_path('views/components/place-card.blade.php'));
    $hero = file_get_contents(resource_path('views/components/place-hero.blade.php'));
    $dashboard = file_get_contents(resource_path('views/components/place-dashboard.blade.php'));

    expect($catalog)
        ->not->toBeFalse()
        ->not->toContain('images.unsplash.com')
        ->and($contentCatalog)
        ->not->toBeFalse()
        ->not->toContain('images.unsplash.com')
        ->and($card)
        ->not->toBeFalse()
        ->toContain(':medium="$place[\'image_medium\']"')
        ->and($hero)
        ->not->toBeFalse()
        ->toContain('<x-responsive-image')
        ->and($dashboard)
        ->not->toBeFalse()
        ->toContain('<x-responsive-image');
});
