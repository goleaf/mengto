<?php

declare(strict_types=1);

use App\Models\ExpertProfile;
use App\Models\ForumEvent;
use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

test('the shared page header exposes one stable semantic identity contract', function () {
    $markup = Blade::render(<<<'BLADE'
        <x-page-header
            eyebrow="Private workspace"
            title="Page identity"
            description="One shared description contract."
            heading-id="page-identity-title"
            meta-label="Page summary"
        >
            <x-slot:meta>
                <span data-test-meta>Two unread</span>
            </x-slot:meta>

            <x-slot:actions>
                <a href="/create" data-test-action>Create</a>
            </x-slot:actions>
        </x-page-header>
    BLADE);

    $document = new DOMDocument;
    $document->loadHTML($markup, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);

    expect($xpath->query('//header[@data-page-identity="canonical" and @aria-labelledby="page-identity-title"]')->length)
        ->toBe(1)
        ->and($xpath->query('//header[@data-page-identity="canonical"]//h1[@id="page-identity-title"]')->length)
        ->toBe(1)
        ->and($xpath->query('//header[@data-page-identity="canonical"]//div[contains(concat(" ", normalize-space(@class), " "), " page-header__meta ") and @aria-label="Page summary"]//*[@data-test-meta]')->length)
        ->toBe(1)
        ->and($xpath->query('//header[@data-page-identity="canonical"]//div[contains(concat(" ", normalize-space(@class), " "), " page-header__actions ")]//*[@data-test-action]')->length)
        ->toBe(1);
});

test('the shared page header supports empty, count, single action, and multiple action states', function () {
    $emptyMarkup = Blade::render(<<<'BLADE'
        <x-page-header eyebrow="Context" title="Empty state" description="No metadata or actions." />
    BLADE);
    $countMarkup = Blade::render(<<<'BLADE'
        <x-page-header eyebrow="Context" title="Count state" description="One prepared count." count="8 shown" />
    BLADE);
    $singleActionMarkup = Blade::render(<<<'BLADE'
        <x-page-header
            eyebrow="Context"
            title="Single action"
            description="One primary action."
            action-label="Create"
            action-href="/create"
        />
    BLADE);
    $multipleActionMarkup = Blade::render(<<<'BLADE'
        <x-page-header eyebrow="Context" title="Multiple actions" description="Two prepared actions.">
            <x-slot:actions>
                <a href="/first" data-test-action="first">First</a>
                <button type="button" data-test-action="second">Second</button>
            </x-slot:actions>
        </x-page-header>
    BLADE);

    expect($emptyMarkup)
        ->toContain('aria-labelledby="page-heading"')
        ->not->toContain('page-header__aside')
        ->and($countMarkup)
        ->toContain('page-header__count', '8 shown')
        ->not->toContain('page-header__actions')
        ->and($singleActionMarkup)
        ->toContain('page-header__actions', 'action--primary', 'href="/create"')
        ->and($multipleActionMarkup)
        ->toContain('data-test-action="first"', 'data-test-action="second"');
});

test('the shared page header escapes long localized content without changing its class contract', function () {
    $longTitle = str_repeat('Очень длинный локализованный заголовок ', 8).'<script>alert(1)</script>';
    $longDescription = str_repeat('Ilgas lokalizuotas puslapio aprašymas ', 12).'<img src=x onerror=alert(1)>';

    $markup = Blade::render(<<<'BLADE'
        <x-page-header
            :eyebrow="$eyebrow"
            :title="$title"
            :description="$description"
        />
    BLADE, [
        'eyebrow' => 'Private workspace',
        'title' => $longTitle,
        'description' => $longDescription,
    ]);

    expect($markup)
        ->toContain('page-header__title', 'page-header__description')
        ->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->toContain('&lt;img src=x onerror=alert(1)&gt;')
        ->not->toContain('<script>', '<img src=x');
});

test('every shared page header caller declares a stable page-specific heading id', function () {
    $violations = [];

    foreach (File::allFiles(resource_path('views')) as $view) {
        $source = $view->getContents();

        if (! str_contains($source, '<x-page-header')) {
            continue;
        }

        if (! str_contains($source, 'heading-id=')) {
            $violations[] = $view->getRelativePathname();
        }
    }

    expect($violations)->toBe([]);
});

test('shared page header semantic props use bound values without double escaping', function () {
    $violations = [];

    foreach (File::allFiles(resource_path('views')) as $view) {
        $source = $view->getContents();

        preg_match_all('/<x-page-header\b[^>]*>/s', $source, $headerTags);

        foreach ($headerTags[0] as $headerTag) {
            if (preg_match('/(?:eyebrow|title|description|action-label|meta-label)="\{\{/', $headerTag) === 1) {
                $violations[] = $view->getRelativePathname();
            }
        }
    }

    expect($violations)->toBe([]);
});

test('every first party get route has exactly one page identity classification', function () {
    $classifications = require base_path('tests/Support/page-identity-route-classification.php');
    $documentedRoutes = array_merge(...array_values($classifications));

    Artisan::call('route:list', [
        '--except-vendor' => true,
        '--json' => true,
    ]);

    $routes = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
    $getRoutes = collect($routes)
        ->filter(fn (array $route): bool => str_contains((string) $route['method'], 'GET'))
        ->pluck('name')
        ->filter(fn (?string $name): bool => $name !== null && $name !== '')
        ->sort()
        ->values()
        ->all();

    $sortedDocumentedRoutes = $documentedRoutes;
    sort($sortedDocumentedRoutes);
    preg_match_all(
        '/^\| `([^`]+)` \| `\/[^`]*` \| `[^`]+` \| `([^`]+)` \|/m',
        File::get(base_path('docs/portal/route-matrix.md')),
        $matrixMatches,
        PREG_SET_ORDER,
    );
    $matrixClassifications = [];

    foreach ($matrixMatches as $matrixMatch) {
        $matrixClassifications[$matrixMatch[1]] = $matrixMatch[2];
    }

    $expectedClassifications = [];

    foreach ($classifications as $classification => $routeNames) {
        foreach ($routeNames as $routeName) {
            $expectedClassifications[$routeName] = $classification;
        }
    }

    ksort($matrixClassifications);
    ksort($expectedClassifications);
    $matrixRoutes = array_keys($matrixClassifications);
    sort($matrixRoutes);

    expect(array_keys($classifications))
        ->toBe([
            'canonical-page',
            'migration-candidate',
            'deliberate-detail-or-profile',
            'authentication-shell',
            'special-document-or-scoped-access',
            'file-response',
            'structured-response',
            'redirect',
        ])
        ->and($documentedRoutes)
        ->toHaveCount(count(array_unique($documentedRoutes)))
        ->and($sortedDocumentedRoutes)
        ->toBe($getRoutes)
        ->and($matrixRoutes)
        ->toBe($getRoutes)
        ->and($matrixClassifications)
        ->toBe($expectedClassifications);
});

test('classified canonical portal pages render the canonical page identity', function (string $routeName) {
    $response = $this->get(route($routeName))->assertOk();
    $xpath = responseXPath($response);
    $header = $xpath->query('//main//header[@data-page-identity="canonical"]')->item(0);
    $heading = $xpath->query('//main//header[@data-page-identity="canonical"]//h1')->item(0);

    expect($xpath->query('//main//header[@data-page-identity="canonical"]')->length, $routeName)
        ->toBe(1)
        ->and($xpath->query('//main//h1')->length, $routeName)
        ->toBe(1)
        ->and($xpath->query('//main//header[@data-page-identity="canonical"]//h1[normalize-space()]')->length, $routeName)
        ->toBe(1)
        ->and($xpath->query('//main//header[contains(concat(" ", normalize-space(@class), " "), " forum-header ") or contains(concat(" ", normalize-space(@class), " "), " care-directory-header ") or contains(concat(" ", normalize-space(@class), " "), " messaging-page__header ")]')->length, $routeName)
        ->toBe(0)
        ->and($heading?->attributes?->getNamedItem('id')?->nodeValue, $routeName)
        ->not->toBeNull()
        ->and($header?->attributes?->getNamedItem('aria-labelledby')?->nodeValue, $routeName)
        ->toBe($heading?->attributes?->getNamedItem('id')?->nodeValue);
})->with([
    'pets' => 'pets.index',
    'medical records' => 'medical-records.index',
    'create medical record' => 'medical-records.create',
    'care journals' => 'care-journals.index',
    'create care journal' => 'care-journals.create',
    'devices' => 'devices.index',
    'connect device' => 'devices.create',
    'places' => 'places.index',
    'lost and found' => 'lost-found.index',
    'create lost and found report' => 'lost-found.create',
    'marketplace' => 'marketplace.index',
    'create marketplace listing' => 'marketplace.create',
    'experts' => 'experts.index',
    'create expert profile' => 'experts.create',
    'expert workspace' => 'experts.dashboard',
    'groups' => 'groups.index',
    'neighbors' => 'neighbors.index',
    'discover' => 'discover.index',
    'notifications' => 'notifications.index',
    'walks' => 'walks.index',
    'circle' => 'circle.index',
    'content feed' => 'content.index',
    'connections' => 'connections.index',
    'pet friends' => 'pet-friends.index',
    'messages' => 'messages.index',
    'knowledge' => 'knowledge.index',
    'social feed' => 'preview.feed',
    'meetups' => 'meetups.index',
]);

test('parameterized expert workflows render the canonical page identity', function (string $routeName) {
    $expert = ExpertProfile::factory()->create([
        'owner_key' => $this->authenticatedUser->actor_key,
    ]);
    $response = $this->get(route($routeName, $expert))->assertOk();
    $xpath = responseXPath($response);
    $header = $xpath->query('//main//header[@data-page-identity="canonical"]')->item(0);
    $heading = $xpath->query('//main//header[@data-page-identity="canonical"]//h1')->item(0);

    expect($xpath->query('//main//header[@data-page-identity="canonical"]')->length, $routeName)
        ->toBe(1)
        ->and($xpath->query('//main//h1')->length, $routeName)
        ->toBe(1)
        ->and($heading?->attributes?->getNamedItem('id')?->nodeValue, $routeName)
        ->not->toBeNull()
        ->and($header?->attributes?->getNamedItem('aria-labelledby')?->nodeValue, $routeName)
        ->toBe($heading?->attributes?->getNamedItem('id')?->nodeValue);
})->with([
    'edit expert profile' => 'experts.edit',
    'book expert' => 'experts.bookings.create',
]);

test('the composer renders its prepared form identity through the canonical header', function () {
    $response = $this->get(route('compose', ['kind' => 'post']))->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->query('//main//header[@data-page-identity="canonical"]')->length)
        ->toBe(1)
        ->and($xpath->query('//main//h1[@id="composer-title"]')->length)
        ->toBe(1)
        ->and($xpath->query('//main//section[@aria-labelledby="composer-title"]')->length)
        ->toBe(1);
});

test('authorized knowledge editor modes render one canonical page identity', function (string $routeName) {
    $administrator = User::factory()->administrator()->create();
    $article = KnowledgeArticle::factory()->create();
    $response = $this->actingAs($administrator)
        ->get(route($routeName, $article))
        ->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->query('//main//header[@data-page-identity="canonical"]')->length, $routeName)
        ->toBe(1)
        ->and($xpath->query('//main//h1[@id="knowledge-guide-editor-heading"]')->length, $routeName)
        ->toBe(1)
        ->and($xpath->query('//main//header[contains(concat(" ", normalize-space(@class), " "), " forum-header ")]')->length, $routeName)
        ->toBe(0);
})->with([
    'create knowledge guide' => 'knowledge.guides.create',
    'edit knowledge guide' => 'knowledge.guides.edit',
    'translate knowledge guide' => 'knowledge.guides.translations.create',
]);

test('event workspaces render the canonical page identity', function (string $routeName) {
    $event = ForumEvent::factory()->create([
        'stable_key' => 'small-dog-social',
        'title' => 'Small dog social',
    ]);
    $response = $this->get(route($routeName, $event))->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->query('//main//header[@data-page-identity="canonical"]')->length, $routeName)
        ->toBe(1)
        ->and($xpath->query('//main//h1')->length, $routeName)
        ->toBe(1)
        ->and($xpath->query('//main//header[contains(concat(" ", normalize-space(@class), " "), " forum-header ")]')->length, $routeName)
        ->toBe(0);
})->with([
    'event detail' => 'meetups.show',
    'legacy small dog event detail' => 'meetups.small_dog_social',
]);

test('the message folder toolbar remains between page identity and the messaging shell', function () {
    $response = $this->get(route('messages.index'))->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->query(
        '//div[contains(concat(" ", normalize-space(@class), " "), " messaging-page ")]'
        .'/header[@data-page-identity="canonical"]'
        .'[following-sibling::nav[contains(concat(" ", normalize-space(@class), " "), " messaging-folders ")]]',
    )->length)->toBe(1)
        ->and($xpath->query(
            '//div[contains(concat(" ", normalize-space(@class), " "), " messaging-page ")]'
            .'/nav[contains(concat(" ", normalize-space(@class), " "), " messaging-folders ")]'
            .'[following-sibling::div[contains(concat(" ", normalize-space(@class), " "), " messaging-shell ")]]',
        )->length)->toBe(1);
});
