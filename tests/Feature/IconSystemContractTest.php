<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

test('the installed lucide package exposes the supported version two icon source', function () {
    $composer = json_decode(File::get(base_path('composer.lock')), true, flags: JSON_THROW_ON_ERROR);
    $lucide = collect($composer['packages'])
        ->firstWhere('name', 'mallardduck/blade-lucide-icons');

    expect($lucide)
        ->toBeArray()
        ->and($lucide['version'])
        ->toStartWith('2.')
        ->and(File::isDirectory(base_path('vendor/mallardduck/blade-lucide-icons/resources/svg/icons')))
        ->toBeTrue();
});

function iconSystemXPath(string $markup): DOMXPath
{
    $document = new DOMDocument;
    $document->loadHTML($markup, LIBXML_NOERROR | LIBXML_NOWARNING);

    return new DOMXPath($document);
}

test('the canonical icon primitive owns size stroke and accessibility states', function () {
    $decorative = Blade::render('<x-ui-icon name="paw-print" size="sm" />');
    $informative = Blade::render('<x-ui-icon name="triangle-alert" label="Important warning" />');

    $decorativeXPath = iconSystemXPath($decorative);
    $informativeXPath = iconSystemXPath($informative);

    expect($decorativeXPath->query(
        '//svg[contains(concat(" ", normalize-space(@class), " "), " ui-icon ")'
        .' and contains(concat(" ", normalize-space(@class), " "), " ui-icon--sm ")'
        .' and @aria-hidden="true"]',
    )->length)
        ->toBe(1)
        ->and($informativeXPath->query(
            '//svg[contains(concat(" ", normalize-space(@class), " "), " ui-icon ")'
            .' and @role="img" and @aria-label="Important warning" and not(@aria-hidden)]',
        )->length)
        ->toBe(1);

    $styles = File::get(resource_path('scss/_components.scss'));

    expect($styles)
        ->toContain('.ui-icon', 'stroke-width: 1.9', '&--sm', '&--lg', '&--hero');
});

test('shared icon-bearing controls delegate rendering to the canonical primitive', function () {
    foreach ([
        'action-control.blade.php',
        'desktop-nav-item.blade.php',
        'empty-state.blade.php',
        'icon-link.blade.php',
        'icon-list.blade.php',
        'icon-text.blade.php',
        'mobile-nav-item.blade.php',
        'notice.blade.php',
        'text-link.blade.php',
    ] as $component) {
        $source = File::get(resource_path('views/components/'.$component));

        expect($source, $component)
            ->toContain('<x-ui-icon')
            ->not->toContain("'lucide-", '<x-lucide-');
    }
});

test('primary navigation renders one consistent icon with every visible destination', function () {
    $response = $this->get(route('preview.feed'))->assertSuccessful();
    $xpath = responseXPath($response);

    expect($xpath->query(
        '//nav[@data-navigation-variant="desktop"]//a[@data-nav-item]'
        .'/svg[contains(concat(" ", normalize-space(@class), " "), " ui-icon ")]',
    )->length)
        ->toBe(13)
        ->and($xpath->query(
            '//nav[@data-navigation-variant="mobile"]//a[@data-nav-item]'
            .'/svg[contains(concat(" ", normalize-space(@class), " "), " ui-icon ")]',
        )->length)
        ->toBe(11)
        ->and($xpath->query(
            '//nav[@data-navigation-variant="desktop"]//a[@data-nav-item]'
            .'[not(svg[@aria-hidden="true"])]',
        )->length)
        ->toBe(0);
});

test('the icon migration debt only moves downward and foreign icon systems stay absent', function () {
    $directLucideInstances = 0;
    $dynamicLucideInstances = 0;
    $canonicalIconInstances = 0;
    $legacyIconClassInstances = 0;
    $legacyStyleSelectorInstances = 0;
    $inlineSvgFiles = [];
    $foreignIconFiles = [];
    $pictogramFiles = [];

    foreach (File::allFiles(resource_path('views')) as $view) {
        if ($view->getExtension() !== 'php' || ! str_ends_with($view->getFilename(), '.blade.php')) {
            continue;
        }

        $source = $view->getContents();
        $relativePath = $view->getRelativePathname();
        $directLucideInstances += preg_match_all('/<x-lucide-[a-z0-9-]+\b/', $source);
        $canonicalIconInstances += preg_match_all('/<x-ui-icon\b/', $source);
        if ($relativePath !== 'components/ui-icon.blade.php') {
            $dynamicLucideInstances += substr_count($source, 'lucide-');
        }
        preg_match_all('/class="([^"]*)"/', $source, $classMatches);

        foreach ($classMatches[1] as $classValue) {
            foreach (preg_split('/\s+/', trim($classValue)) ?: [] as $className) {
                if (in_array($className, ['icon', 'icon--xs', 'icon--sm'], true)) {
                    $legacyIconClassInstances++;
                }
            }
        }

        if (preg_match('/<svg\b/i', $source) === 1
            && $relativePath !== 'components/medical-weight-chart.blade.php') {
            $inlineSvgFiles[] = $relativePath;
        }

        if (preg_match('/(?:heroicon|font-awesome|material-symbol|\bmdi-|\bbi-)/i', $source) === 1) {
            $foreignIconFiles[] = $relativePath;
        }

        if (preg_match('/[←→↗↻×✓✔✕✖⚠★☆♥♡]/u', $source) === 1) {
            $pictogramFiles[] = $relativePath;
        }
    }

    foreach (File::allFiles(resource_path('scss')) as $style) {
        if ($style->getExtension() !== 'scss') {
            continue;
        }

        $legacyStyleSelectorInstances += preg_match_all(
            '/\.icon(?=--(?:xs|sm)\b|[\s,{:\[])/',
            $style->getContents(),
        );
    }

    expect($directLucideInstances)->toBe(0)
        ->and($dynamicLucideInstances)->toBe(0)
        ->and($canonicalIconInstances)->toBeGreaterThanOrEqual(828)
        ->and($legacyIconClassInstances)->toBe(0)
        ->and($legacyStyleSelectorInstances)->toBe(0)
        ->and($inlineSvgFiles)->toBe([])
        ->and($foreignIconFiles)->toBe([])
        ->and($pictogramFiles)->toBe([]);
});
