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

test('the repeatable page identity browser matrix covers every priority surface and accessibility mode', function () {
    $source = File::get(base_path('scripts/accessibility-browser-check.mjs'));
    $package = File::get(base_path('package.json'));

    preg_match_all(
        "/\\{ path: '([^']+)', slug: '[^']+', label: '[^']+' \\}/",
        $source,
        $routeMatches,
    );

    expect($routeMatches[1])
        ->toBe([
            '/pets',
            '/medical-records',
            '/care-journals',
            '/meetups',
            '/places',
            '/lost-found',
            '/marketplace',
            '/experts',
            '/forum',
            '/groups',
            '/neighbors',
            '/discover',
            '/messages',
        ])
        ->and($source)
        ->toContain(
            "const pageIdentityOnly = process.argv.includes('--page-identity-only')",
            "{ label: '320-en', locale: 'en', width: 320",
            "{ label: '375-ru', locale: 'ru', width: 375",
            "{ label: '768-lt', locale: 'lt', width: 768",
            "{ label: '1024-ru-forced-colors', locale: 'ru', width: 1024",
            "{ label: '1280-en-200-percent', locale: 'en', width: 640, height: 450, screenWidth: 1280, screenHeight: 900, zoom: 2",
            "{ label: '1440-en', locale: 'en', width: 1440",
            "{ label: '1920-lt', locale: 'lt', width: 1920",
            'const englishIdentityCopy = new Map()',
            'deviceScaleFactor: viewport.zoom ?? 1',
            'document title was not localized',
            'heading was not localized',
            "{ name: 'prefers-reduced-motion', value: 'reduce' }",
            "{ name: 'forced-colors', value: viewport.forcedColors ? 'active' : 'none' }",
            "join(outputDirectory, 'page-identity-report.json')",
        )
        ->and($package)
        ->toContain('"test:browser:page-identity": "php scripts/run-browser-check.php page-identity"');
});

test('priority page identity copy is translated instead of falling back to English', function () {
    $contracts = [
        'ui' => [
            'active_local_searches_a0b657fac3',
            'ask_well_find_what_lasts_3c2fdf9b45',
            'buy_exchange_rehome_or_book_without_exposing_your_a7174cb664',
            'care_journals_efcbb402a3',
            'community_knowledge_31eb615b90',
            'community_marketplace_1525148f3c',
            'compare_scope_species_independently_checked_credentials_availability_lan_8ad672b4c3',
            'create_journal_0be6b9b3a5',
            'create_listing_815d30caa6',
            'create_professional_profile_30276b75d3',
            'find_the_right_specialist_for_this_pet_21bb34d7d0',
            'groups_brand_2cc8a218be',
            'lost_found_217c655848',
            'messages_and_calls_brand_d76656782d',
            'neighbors_brand_de44b47ada',
            'new_health_record_376edfa614',
            'new_message_78f5975a5d',
            'pet_health_records_911c3e19be',
            'private_care_workspace_12776f8bcf',
            'private_family_workspace_521e77339e',
            'professional_workspace_eb8eb6dde6',
            'questions_field_notes_expert_context_and_practical_guides_5f0c917aa8',
            'report_a_sighting_join_a_coordinated_task_or_e5e0bbe8c2',
            'report_an_animal_6188a5d89e',
            'today_s_feeding_water_walks_rest_toilet_activity_dc1cdec032',
            'useful_things_and_trusted_pet_services_0b2d0b997a',
            'vaccinations_medication_schedules_measurements_visits_and_original_docum_72518c6620',
            'verified_professional_community_f3f93b61ff',
        ],
        'messages' => [
            'communities_with_a_purpose_b2d3a5a7b6',
            'compare_parks_dog_runs_routes_clinics_services_shelters__f8d00ec18d',
            'expert_community_b9f71bc7cd',
            'explore_local_breed_care_adoption_and_interest_groups_wi_219f9d1209',
            'find_nearby_owners_who_share_your_routes_routines_and_ap_662718d443',
            'find_your_people_and_build_something_useful_b7d93d9c88',
            'groups_pawcircle_2cc8a218be',
            'lost_found_217c655848',
            'map_and_place_catalog_e874309a26',
            'marketplace_c608981d8d',
            'meet_the_people_behind_the_pets_a0afb859f3',
            'messages_and_calls_2bda9155c7',
            'pet_health_records_911c3e19be',
            'places_map_pawcircle_3cab208400',
            'plan_the_next_place_with_your_pet_47805e2905',
            'portland_neighbors_c6674bf8c7',
            'private_care_journals_f718a9186c',
            'private_communication_b3ecd460d1',
            'talk_to_pet_people_family_specialists_groups_and_event_o_7a75ff5b8e',
        ],
    ];

    foreach ($contracts as $catalog => $keys) {
        $english = require lang_path("en/{$catalog}.php");

        expect($english)->toHaveKeys($keys);

        foreach (['lt', 'ru'] as $locale) {
            $translated = require lang_path("{$locale}/{$catalog}.php");

            expect($translated)->toHaveKeys($keys);

            foreach ($keys as $key) {
                expect($translated[$key])
                    ->not->toBe($english[$key])
                    ->not->toBe('');
            }
        }
    }
});

test('the shared control focus ring remains visible in forced colors', function () {
    expect(File::get(resource_path('scss/_tokens.scss')))->toContain(
        '@mixin focus-ring',
        'outline: 2px solid transparent;',
        '@media (forced-colors: active)',
        'outline-color: Highlight;',
        'box-shadow: none;',
    );
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
