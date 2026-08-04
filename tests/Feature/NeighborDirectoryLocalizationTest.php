<?php

declare(strict_types=1);

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

const NEIGHBOR_DIRECTORY_IDENTITY_KEYS = [
    'summary.closest.detail',
    'card.brand_initials',
    'catalog.ari.name',
    'catalog.ari.neighborhood',
    'catalog.noah.name',
    'catalog.noah.neighborhood',
    'catalog.lena.name',
    'catalog.lena.neighborhood',
    'catalog.priya.name',
    'catalog.priya.neighborhood',
];

test('the neighbor directory contract is complete in every supported locale', function (): void {
    $english = Arr::dot(require lang_path('en/neighbors.php'));
    $english = Arr::where(
        $english,
        static fn (mixed $value, string $key): bool => ! str_starts_with($key, 'profile.'),
    );

    expect($english)->toHaveCount(71);

    foreach (['lt', 'ru'] as $locale) {
        $localized = Arr::where(
            Arr::dot(require lang_path("{$locale}/neighbors.php")),
            static fn (mixed $value, string $key): bool => ! str_starts_with($key, 'profile.'),
        );

        expect(array_keys($localized), $locale)->toBe(array_keys($english));

        foreach ($localized as $key => $value) {
            expect($value, "{$locale}.neighbors.{$key}")->toBeString()->not->toBe('');

            if (! in_array($key, NEIGHBOR_DIRECTORY_IDENTITY_KEYS, true)) {
                expect($value, "{$locale}.neighbors.{$key}")->not->toBe($english[$key]);
            }
        }
    }
});

test('the neighbor directory renders localized system and fixture copy', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $copy = require lang_path("{$locale}/neighbors.php");
    $response = $this->get(route('neighbors.index'))->assertOk();

    foreach ([
        data_get($copy, 'page.eyebrow'),
        data_get($copy, 'page.heading'),
        data_get($copy, 'page.description'),
        data_get($copy, 'summary.label'),
        data_get($copy, 'filters.toolbar_label'),
        data_get($copy, 'search.placeholder'),
        data_get($copy, 'results.title'),
        data_get($copy, 'catalog.ari.category'),
        data_get($copy, 'catalog.noah.status'),
        data_get($copy, 'catalog.lena.pet'),
        data_get($copy, 'catalog.priya.image_alt'),
    ] as $value) {
        $response->assertSee((string) $value);
    }

    $xpath = responseXPath($response);

    expect($xpath->query('//*[@data-neighbor-summary]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-neighbor-filters]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-neighbor-results]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-neighbor-card]')->length)->toBe(4)
        ->and($xpath->query('//*[@data-neighbor-card-category]//*[@data-ui-icon]')->length)->toBe(4);
})->with(['lt', 'ru']);

test('neighbor filters and closest sorting use locale independent fields', function (string $locale): void {
    $this->authenticatedUser->update(['locale' => $locale]);

    $dogPeople = $this->get(route('neighbors.index', ['filter' => 'dog-people']))->assertOk();
    $dogXPath = responseXPath($dogPeople);

    expect($dogXPath->query('//*[@data-neighbor-card]')->length)->toBe(2)
        ->and($dogPeople->getContent())->toContain('Ari Jensen', 'Noah Patel')
        ->not->toContain('Lena Brooks', 'Priya Shah');

    $catPeople = $this->get(route('neighbors.index', ['filter' => 'cat-people']))->assertOk();
    $catXPath = responseXPath($catPeople);

    expect($catXPath->query('//*[@data-neighbor-card]')->length)->toBe(1)
        ->and($catPeople->getContent())->toContain('Lena Brooks')
        ->not->toContain('Ari Jensen', 'Noah Patel', 'Priya Shah');

    $sorted = responseXPath($this->get(route('neighbors.index', ['sort' => 'closest']))->assertOk());
    $names = array_map(
        static fn (DOMNode $node): string => trim($node->textContent),
        iterator_to_array($sorted->query('//*[@data-neighbor-card]//*[@data-card-heading]')),
    );

    expect($names)->toBe(['Ari Jensen', 'Noah Patel', 'Lena Brooks', 'Priya Shah']);
})->with(['lt', 'ru']);

test('neighbor directory source uses its domain, stable filters, numeric distance, and browser ratchet', function (): void {
    $previewSource = File::get(app_path('Services/PreviewService.php'));
    preg_match(
        '/public function neighborDirectoryData\(\): array(?<body>.*?)(?=public function ariNeighborProfileData)/s',
        $previewSource,
        $directory,
    );
    preg_match(
        '/private function directoryNeighbors\(\): array(?<body>.*?)(?=private function posts)/s',
        $previewSource,
        $catalog,
    );

    $source = ($directory['body'] ?? '').($catalog['body'] ?? '');

    expect($source)
        ->not->toBe('')
        ->not->toContain("__('messages.", "'training'", "'fostering'", "'rabbits'")
        ->toContain(
            "__('neighbors.",
            "'distance_value' =>",
            "'search_tokens' =>",
            "'category_icon' =>",
        );

    foreach ([
        resource_path('views/neighbors/index.blade.php'),
        resource_path('views/components/neighbor-directory-results.blade.php'),
        resource_path('views/components/neighbor-card.blade.php'),
    ] as $path) {
        expect(File::get($path), $path)
            ->not->toContain("__('ui.", "__('messages.")
            ->toContain("__('neighbors.");
    }

    $filterSource = File::get(app_path('Services/DirectoryFilter.php'));
    expect($filterSource)->toContain(
        "\$left['distance_value'] ?? \$left['distance']",
        "\$right['distance_value'] ?? \$right['distance']",
    );

    $browser = File::get(base_path('scripts/accessibility-browser-check.mjs'));
    expect($browser)->toContain(
        'englishNeighborCopy',
        'neighborCopy.length === 43',
        'English neighbor body fallback remains.',
    );
});

test('neighbor directory keeps its query count bounded', function (): void {
    $queries = [];
    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->get(route('neighbors.index'))->assertOk();

    expect($queries)->toHaveCount(2);
});
