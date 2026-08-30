<?php

declare(strict_types=1);

use App\Models\ExpertProfile;
use App\Models\ForumEvent;
use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

test('canonical requirements assign one stable product id to global page identity', function () {
    $productRequirements = File::get(base_path('docs/product-requirements.md'));
    $complianceMatrix = File::get(base_path('docs/requirements/compliance-matrix.md'));
    $deliveryPlan = File::get(base_path('docs/plans/global-page-identity-standardization-plan.md'));

    expect($productRequirements)
        ->toContain('| PRD-UI-001 |')
        ->and($complianceMatrix)
        ->toContain('| PRD-UI-001 |')
        ->and($deliveryPlan)
        ->toContain('`PRD-UI-001`');
});

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

test('canonical page chrome renders prepared identities and navigation without database queries', function () {
    DB::flushQueryLog();
    DB::enableQueryLog();

    $markup = Blade::render(<<<'BLADE'
        <x-page-header
            eyebrow="Directory"
            title="Prepared identity"
            description="Prepared description"
            heading-id="prepared-identity-heading"
        >
            <x-slot:actions>
                <a href="/create">Create</a>
            </x-slot:actions>
        </x-page-header>
        <x-site-header :owner="$owner" active-section="messages" />
        <x-primary-navigation active-section="messages" variant="mobile" />
    BLADE, [
        'owner' => [
            'name' => 'Mia Carter',
            'avatar' => '/images/demo-avatar.webp',
        ],
    ]);

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($markup)->toContain('data-page-identity="canonical"', 'data-site-header')
        ->and($queries)->toBeEmpty();
});

test('representative directory detail and workspace routes retain recorded query baselines', function () {
    $queryCount = function (string $routeName, array $parameters = []): int {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get(route($routeName, $parameters))->assertOk();
        $count = count(DB::getQueryLog());

        DB::disableQueryLog();

        return $count;
    };

    expect([
        'directory:messages.index' => $queryCount('messages.index'),
        'detail:messages.details' => $queryCount('messages.details', ['conversation' => 'ari']),
        'workspace:experts.dashboard' => $queryCount('experts.dashboard'),
    ])->toBe([
        'directory:messages.index' => 4,
        'detail:messages.details' => 4,
        'workspace:experts.dashboard' => 2,
    ]);
});

test('the unreachable historical messaging presentation cohort stays removed', function () {
    $historicalFiles = [
        app_path('Services/ConversationPresenter.php'),
        resource_path('views/messages/details.blade.php'),
        resource_path('views/components/call-consent-panel.blade.php'),
        resource_path('views/components/message-center-layout.blade.php'),
        resource_path('views/components/conversation-list.blade.php'),
        resource_path('views/components/conversation-toolbar.blade.php'),
        resource_path('views/components/conversation-item.blade.php'),
        resource_path('views/components/message-thread.blade.php'),
        resource_path('views/components/message-thread-header.blade.php'),
        resource_path('views/components/message-context.blade.php'),
        resource_path('views/components/thread-message-list.blade.php'),
        resource_path('views/components/message-bubble.blade.php'),
        resource_path('views/components/message-composer.blade.php'),
    ];

    expect(array_values(array_filter(
        $historicalFiles,
        static fn (string $path): bool => File::exists($path),
    )))->toBe([])
        ->and(File::get(app_path('Services/PreviewService.php')))
        ->not->toContain(
            'ConversationPresenter',
            'conversationDetailsData(',
            'messageCenterData(',
        );
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
            'accepted_a00fb0c507',
            'active_local_searches_a0b657fac3',
            'all_a52ace420f',
            'all_languages_acce3d0e30',
            'all_topics_29366ff597',
            'ask_well_find_what_lasts_3c2fdf9b45',
            'ask_a_question_3a533d7ef8',
            'buy_exchange_rehome_or_book_without_exposing_your_a7174cb664',
            'care_journals_efcbb402a3',
            'community_knowledge_31eb615b90',
            'community_marketplace_1525148f3c',
            'compare_scope_species_independently_checked_credentials_availability_lan_8ad672b4c3',
            'create_journal_0be6b9b3a5',
            'create_listing_815d30caa6',
            'create_professional_profile_30276b75d3',
            'find_the_right_specialist_for_this_pet_21bb34d7d0',
            'english_ba118bf7fc',
            'expert_reply_6da9cb7be8',
            'forum_summary_b01b2e0a58',
            'forum_topics_dfec5e5f89',
            'groups_brand_2cc8a218be',
            'knowledge_and_notifications_b349fb1522',
            'knowledge_dcb3e1c00e',
            'knowledge_desk_518296addd',
            'lithuanian_8625f6a206',
            'lost_found_217c655848',
            'messages_and_calls_brand_d76656782d',
            'neighbors_brand_de44b47ada',
            'new_health_record_376edfa614',
            'new_message_78f5975a5d',
            'no_categories_available_a557500f61',
            'no_filters_available_dc23b63725',
            'no_forum_statistics_yet_0b02e01738',
            'no_matching_discussion_yet_1dee81e8c3',
            'no_new_updates_5a45a6c539',
            'no_reviewed_guides_yet_3d9f862601',
            'no_sort_options_available_0b2341d59b',
            'no_subcategories_9f1010c1a3',
            'no_topic_tags_f441952188',
            'pet_health_records_911c3e19be',
            'private_care_workspace_12776f8bcf',
            'private_family_workspace_521e77339e',
            'professional_workspace_eb8eb6dde6',
            'questions_field_notes_expert_context_and_practical_guides_5f0c917aa8',
            'report_a_sighting_join_a_coordinated_task_or_e5e0bbe8c2',
            'report_an_animal_6188a5d89e',
            'russian_5bcc40adf6',
            'search_forum_aae59cf0ad',
            'search_questions_pets_places_or_exact_phrases_5190090592',
            'start_a_topic_c27175f4e0',
            'topic_activity_e7e514dfe2',
            'topic_filters_9250e8d56b',
            'topic_language_8d3b8b5b39',
            'topic_sorting_199040afbe',
            'today_s_feeding_water_walks_rest_toilet_activity_dc1cdec032',
            'try_a_broader_phrase_or_start_a_focused_a511e7e0d2',
            'useful_things_and_trusted_pet_services_0b2d0b997a',
            'vaccinations_medication_schedules_measurements_visits_and_original_docum_72518c6620',
            'verified_professional_community_f3f93b61ff',
            'with_b708724a23',
            'your_updates_438558961b',
        ],
        'messages' => [
            'ask_the_community_pawcircle_81f7f8f052',
            'communities_with_a_purpose_b2d3a5a7b6',
            'compare_parks_dog_runs_routes_clinics_services_shelters__f8d00ec18d',
            'expert_community_b9f71bc7cd',
            'edit_topic_pawcircle_aea297c81d',
            'expert_replies_c909ce7644',
            'explore_local_breed_care_adoption_and_interest_groups_wi_219f9d1209',
            'find_nearby_owners_who_share_your_routes_routines_and_ap_662718d443',
            'find_your_people_and_build_something_useful_b7d93d9c88',
            'groups_pawcircle_2cc8a218be',
            'lost_found_217c655848',
            'map_and_place_catalog_e874309a26',
            'marketplace_c608981d8d',
            'meet_the_people_behind_the_pets_a0afb859f3',
            'messages_and_calls_2bda9155c7',
            'need_an_answer_8824024b01',
            'open_topics_5207eb9e06',
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

test('first party English demo content declares a truthful source locale', function () {
    $events = File::get(database_path('seeders/ForumEventDemoSeeder.php'));
    $expertSessions = File::get(database_path('seeders/ForumExpertSessionDemoSeeder.php'));

    expect($events)
        ->toContain("'locale' => 'en'")
        ->not->toContain("'locale' => \$index % 3")
        ->and($expertSessions)
        ->toContain("'locale' => 'en'")
        ->not->toContain("'locale' => 'lt'");
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
            'directory',
            'detail',
            'workspace',
            'editor',
            'dashboard',
            'settings',
            'authentication',
            'shared access',
            'print/export',
            'deliberate special case',
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
