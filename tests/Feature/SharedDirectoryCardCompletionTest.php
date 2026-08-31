<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;

test('every card-like component has one reviewed family classification', function (): void {
    $inventory = require base_path('tests/Fixtures/shared-directory-card-families.php');
    $primitiveFiles = [
        'resources/views/components/card-action-row.blade.php',
        'resources/views/components/card-description.blade.php',
        'resources/views/components/card-heading.blade.php',
        'resources/views/components/card-media.blade.php',
        'resources/views/components/directory-card.blade.php',
    ];
    $cardFiles = collect(File::glob(resource_path('views/components/*card*.blade.php')))
        ->map(static fn (string $path): string => 'resources/views/'.str_replace(
            '\\',
            '/',
            str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $path),
        ))
        ->reject(static fn (string $path): bool => in_array($path, $primitiveFiles, true))
        ->push('resources/views/components/share-recipient-item.blade.php')
        ->sort()
        ->values()
        ->all();
    $allowed = [
        'adopt shared shell',
        'adopt shared leaf components',
        'retain domain-specific implementation',
        'merge equivalent implementations',
        'retire obsolete implementation',
    ];

    expect(array_keys($inventory))->toBe($cardFiles);

    foreach ($inventory as $classification) {
        expect($allowed)->toContain($classification);
    }
});

test('the direct directory shell set is closed and cannot duplicate raw card headings', function (): void {
    $expected = [
        'resources/views/components/group-card.blade.php',
        'resources/views/components/meetup-card.blade.php',
        'resources/views/components/neighbor-card.blade.php',
        'resources/views/components/pet-directory-card.blade.php',
    ];
    $consumers = collect(File::allFiles(resource_path('views')))
        ->filter(static fn (SplFileInfo $file): bool => str_contains($file->getContents(), '<x-directory-card'))
        ->map(static fn (SplFileInfo $file): string => 'resources/views/'.str_replace('\\', '/', $file->getRelativePathname()))
        ->sort()
        ->values()
        ->all();

    expect($consumers)->toBe($expected);

    foreach ($expected as $path) {
        $source = File::get(base_path($path));

        expect($source, $path)
            ->toContain('<x-card-heading')
            ->not->toMatch('/<h[23]\b/i');
    }
});

test('compatible audited families use only the declared shared leaves', function (): void {
    $contracts = [
        'resources/views/components/place-card.blade.php' => ['<x-card-media', '<x-card-heading', '<x-card-description', '<x-card-action-row'],
        'resources/views/components/search-case-card.blade.php' => ['<x-responsive-image', '<x-card-heading', '<x-card-description'],
        'resources/views/components/discovery-result-card.blade.php' => ['<x-linked-media', '<x-responsive-image', '<x-card-heading', '<x-card-description', '<x-card-action-row'],
        'resources/views/components/expert-card.blade.php' => ['<x-linked-media', '<x-avatar', '<x-card-heading', '<x-card-action-row'],
        'resources/views/components/listing-card.blade.php' => ['<x-responsive-image', '<x-card-heading', '<x-card-description', '<x-card-action-row'],
        'resources/views/components/knowledge-article-card.blade.php' => ['<x-card-heading', '<x-card-description'],
    ];

    foreach ($contracts as $path => $needles) {
        $source = File::get(base_path($path));

        expect($source, $path)->not->toContain('<x-directory-card');

        foreach ($needles as $needle) {
            expect($source, $path)->toContain($needle);
        }
    }
});

test('private and operational card shells adopt safe leaves without losing their topology', function (): void {
    $contracts = [
        'resources/views/components/medical-record-card.blade.php' => ['medical-record-card', '<x-responsive-image', '<x-card-heading'],
        'resources/views/components/care-journal-card.blade.php' => ['care-journal-card', '<x-responsive-image', '<x-card-heading', '<x-card-action-row'],
        'resources/views/components/device-card.blade.php' => ['device-card', '<x-card-heading'],
        'resources/views/components/booking-content.blade.php' => ['booking-content', '<x-avatar'],
    ];

    foreach ($contracts as $path => $needles) {
        $source = File::get(base_path($path));

        expect($source, $path)->not->toContain('<x-directory-card');

        foreach ($needles as $needle) {
            expect($source, $path)->toContain($needle);
        }
    }
});

test('shared headings are escaped text with bounded semantic and spacing variants', function (): void {
    $html = Blade::render(<<<'BLADE'
        <x-card-heading :title="$unsafe" level="2" spacing="compact" data-test-heading />
    BLADE, ['unsafe' => new HtmlString('<em>Unsafe</em>')]);
    $xpath = sharedDirectoryCardXPath($html);
    $heading = $xpath->query('//h2[@data-card-heading and @data-test-heading]')->item(0);

    expect($heading)->not->toBeNull()
        ->and($xpath->query('//em')->length)->toBe(0)
        ->and($html)->toContain('&lt;em&gt;Unsafe&lt;/em&gt;')
        ->and(' '.$heading?->attributes?->getNamedItem('class')?->nodeValue.' ')->toContain(' mt-1.5 ');
});

test('linked media refuses to create an unnamed interactive target', function (mixed $label): void {
    $html = Blade::render(
        '<x-linked-media href="/pets/scout" :label="$label"><span>Pet</span></x-linked-media>',
        ['label' => $label],
    );
    $xpath = sharedDirectoryCardXPath($html);

    expect($xpath->query('//*[@data-linked-media]')->length)->toBe(0)
        ->and($xpath->query('//*[@data-passive-media]')->length)->toBe(1);
})->with([
    'null' => null,
    'empty' => '',
    'whitespace' => '   ',
]);

test('server backed action controls expose disabled loading and repeat submission contracts', function (): void {
    $html = Blade::render(<<<'BLADE'
        <x-action-control
            endpoint="/actions"
            label="Save"
            loading-label="Saving"
            :disabled="true"
        />
    BLADE);
    $xpath = sharedDirectoryCardXPath($html);
    $form = $xpath->query('//form[@data-action-form]')->item(0);
    $button = $xpath->query('//form[@data-action-form]/button[@data-action-submit]')->item(0);

    expect($form)->not->toBeNull()
        ->and($form?->attributes?->getNamedItem('data-action-pending')?->nodeValue)->toBe('false')
        ->and($button)->not->toBeNull()
        ->and($button?->attributes?->getNamedItem('disabled'))->not->toBeNull()
        ->and($button?->attributes?->getNamedItem('aria-disabled')?->nodeValue)->toBe('true')
        ->and($xpath->query('//form[@data-action-form]//*[@data-action-label]')->length)->toBe(1)
        ->and($xpath->query('//form[@data-action-form]//*[@data-action-loading-label]')->length)->toBe(1);

    expect(File::get(resource_path('js/app.js')))->toContain("import './action-forms';")
        ->and(File::get(resource_path('js/action-forms.js')))
        ->toContain('data-action-form', 'data-action-pending', 'preventDefault', 'aria-busy');
});

test('two proven share rows use a separate compact composition', function (): void {
    $component = File::get(resource_path('views/components/compact-resource-row.blade.php'));

    expect($component)
        ->toContain('data-compact-resource-row', '<article', '<h3')
        ->not->toContain('<x-directory-card', 'media=', 'footer=');

    foreach (['share-channel-card.blade.php', 'share-recipient-item.blade.php'] as $file) {
        expect(File::get(resource_path('views/components/'.$file)), $file)
            ->toContain('<x-compact-resource-row')
            ->not->toMatch('/<article\b/');
    }
});

test('proved obsolete card source and selectors are retired together', function (): void {
    expect(File::exists(resource_path('views/components/person-summary.blade.php')))->toBeFalse()
        ->and(File::get(resource_path('scss/_content.scss')))->not->toContain('.person-summary')
        ->and(File::get(resource_path('scss/_feed.scss')))->not->toContain('.card-icon-button')
        ->and(File::get(base_path('tests/Fixtures/linked-media-navigation.php')))->not->toContain('person-summary.blade.php');
});

function sharedDirectoryCardXPath(string $html): DOMXPath
{
    $document = new DOMDocument;
    $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

    return new DOMXPath($document);
}
