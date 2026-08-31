<?php

declare(strict_types=1);

use App\Models\Credential;
use App\Models\ExpertProfile;
use App\Models\ForumAnswer;
use App\Models\ForumEvent;
use App\Models\ForumTopic;
use App\Models\KnowledgeArticle;
use App\Models\Listing;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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

test('detail back links use one labelled navigation contract without changing destinations', function () {
    $markup = Blade::render(<<<'BLADE'
        <x-detail-navigation href="/authorized-return" label="Return to record">
            <span data-test-trail>Private</span>
        </x-detail-navigation>
    BLADE);

    $document = new DOMDocument;
    $document->loadHTML($markup, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);

    expect($xpath->query('//nav[@data-detail-navigation and normalize-space(@aria-label)]')->length)
        ->toBe(1)
        ->and($xpath->query('//nav[@data-detail-navigation]//a[@href="/authorized-return"]')->length)
        ->toBe(1)
        ->and($xpath->query('//nav[@data-detail-navigation]//*[@data-test-trail]')->length)
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
            'name' => 'Test Member',
            'initials' => 'TM',
            'avatar' => '/images/demo-avatar.webp',
            'profile_url' => '/members/test-member',
        ],
    ]);

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($markup)->toContain('data-page-identity="canonical"', 'data-site-header')
        ->and($queries)->toBeEmpty();
});

test('representative directory detail and workspace routes retain recorded query baselines', function () {
    $expert = ExpertProfile::factory()->create();
    Service::factory()->create(['expert_profile_id' => $expert->id]);
    Credential::factory()->create(['expert_profile_id' => $expert->id]);
    ForumAnswer::factory()->create([
        'topic_id' => ForumTopic::factory(),
        'expert_profile_id' => $expert->id,
    ]);

    $queryCount = function (string $routeName, array $parameters = []): int {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $this->get(route($routeName, $parameters))->assertOk();
            $count = count(DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }

        return $count;
    };

    expect([
        'directory:experts.index' => $queryCount('experts.index'),
        'detail:experts.show' => $queryCount('experts.show', ['expertProfile' => $expert]),
    ])->toBe([
        'directory:experts.index' => 6,
        'detail:experts.show' => 13,
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

test('retired header and call consent selector families stay removed', function () {
    $sources = collect([
        ...File::allFiles(resource_path('views')),
        ...File::allFiles(resource_path('scss')),
    ])->map(fn (SplFileInfo $file): string => $file->getContents())->implode("\n");

    expect($sources)
        ->not->toContain(
            'forum-header',
            'care-directory-header',
            'messaging-page__header',
            'call-consent',
        )
        ->and(File::get(resource_path('views/components/section-heading.blade.php')))
        ->toContain('section-heading__description');
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
            "path: '/experts/dr-emilia-vaitke'",
            "path: '/groups/trail-tails'",
            "path: '/places/vingis-quiet-loop'",
            "path: '/lost-found/scout-missing-vingis-park'",
            "path: '/medical-records/scout-health'",
            "path: '/care-journals/scout-care'",
            "path: '/devices/scout-trail-gps'",
            "currentPath === '/confirm-password'",
            "location.pathname') === route.path",
            "'Input.dispatchKeyEvent'",
            "matches(':focus-visible')",
            "document.querySelector('[data-detail-navigation]')",
            'const englishIdentityCopy = new Map()',
            "header?.querySelector('[data-page-header-content]')",
            'deviceScaleFactor: viewport.zoom ?? 1',
            'document title was not localized',
            'heading was not localized',
            "{ name: 'prefers-reduced-motion', value: 'reduce' }",
            "{ name: 'forced-colors', value: viewport.forcedColors ? 'active' : 'none' }",
            "client.on('Network.responseReceived'",
            'entry.url',
            'resourceErrors',
            "join(outputDirectory, 'page-identity-report.json')",
            'consoleErrors.length === 0 && resourceErrors.length === 0',
        )
        ->and($package)
        ->toContain('"test:browser:page-identity": "php scripts/run-browser-check.php page-identity"');

    $reportPosition = strpos($source, "join(outputDirectory, 'page-identity-report.json')");
    $resourceAssertionPosition = is_int($reportPosition)
        ? strpos($source, 'consoleErrors.length === 0 && resourceErrors.length === 0', $reportPosition)
        : false;

    expect($reportPosition)->toBeInt()
        ->and($resourceAssertionPosition)->toBeInt()
        ->and($reportPosition)->toBeLessThan($resourceAssertionPosition);
});

test('priority page identity copy is translated instead of falling back to English', function () {
    $contracts = [
        'ui' => [
            'accepted',
            'active_local_searches',
            'all',
            'all_languages',
            'all_topics',
            'ask_well_find_what_lasts',
            'ask_a_question',
            'buy_exchange_rehome_or_book_without_exposing_your_phone_number_or_home_address_before_both_sides_agree',
            'care_journals',
            'community_knowledge',
            'community_marketplace',
            'compare_scope_species_independently_checked_credentials_availability_language_and_price_before_sharing_any_private_pet_information',
            'create_journal',
            'create_listing',
            'create_professional_profile',
            'find_the_right_specialist_for_this_pet',
            'english',
            'expert_reply',
            'forum_summary',
            'forum_topics',
            'groups_brand',
            'knowledge_and_notifications',
            'knowledge',
            'knowledge_desk',
            'lithuanian',
            'lost_found',
            'messages_and_calls_brand',
            'neighbors_brand',
            'new_health_record',
            'new_message',
            'no_categories_available_sentence',
            'no_filters_available',
            'no_forum_statistics_yet',
            'no_matching_discussion_yet',
            'no_new_updates',
            'no_reviewed_guides_yet',
            'no_sort_options_available',
            'no_subcategories',
            'no_topic_tags',
            'pet_health_records',
            'private_care_workspace',
            'private_family_workspace',
            'professional_workspace',
            'questions_field_notes_expert_context_and_practical_guides_that_remain_useful_after_the_feed_moves_on',
            'report_a_sighting_join_a_coordinated_task_or_help_a_found_animal_reach_a_verified_owner_without_exposing_private_addresses',
            'report_an_animal',
            'russian',
            'search_forum',
            'search_questions_pets_places_or_exact_phrases',
            'start_a_topic',
            'topic_activity',
            'topic_filters',
            'topic_language',
            'topic_sorting',
            'today_s_feeding_water_walks_rest_toilet_activity_routines_and_handoffs_for_every_pet_you_manage',
            'try_a_broader_phrase_or_start_a_focused_question_with_the_details_that_make_your_case_different',
            'useful_things_and_trusted_pet_services',
            'vaccinations_medication_schedules_measurements_visits_and_original_documents',
            'verified_professional_community',
            'with',
            'your_updates',
        ],
        'messages' => [
            'ask_the_community_brand',
            'communities_with_a_purpose',
            'compare_parks_dog_runs_routes_clinics_services_shelters_stores_and_pet_friendly_places_without_exposing_your_home_or_movement_history',
            'expert_community',
            'edit_topic_brand',
            'expert_replies',
            'explore_local_breed_care_adoption_and_interest_groups_with_clear_privacy_and_moderation_boundaries',
            'find_nearby_owners_who_share_your_routes_routines_and_approach_to_everyday_care',
            'find_your_people_and_build_something_useful',
            'groups_brand',
            'legacy_compatibility_contribution_retained_for_review',
            'legacy_question_retained_for_moderation_review',
            'legacy_review_retained_for_moderation_review',
            'legacy_warning_retained_for_moderation_review',
            'lost_found',
            'map_and_place_catalog',
            'marketplace',
            'meet_the_people_behind_the_pets',
            'messages_and_calls',
            'need_an_answer',
            'open_topics',
            'pet_health_records',
            'places_map_brand',
            'plan_the_next_place_with_your_pet',
            'portland_neighbors',
            'private_care_journals',
            'private_communication',
            'talk_to_pet_people_family_specialists_groups_and_event_organizers_without_exposing_personal_contact_details',
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

test('remaining priority body copy has no English fallback in Lithuanian or Russian', function () {
    $surfaces = [
        'pets' => [
            resource_path('views/pets/index.blade.php'),
            resource_path('views/components/pet-directory-card.blade.php'),
        ],
        'meetups' => [
            resource_path('views/livewire/forum/forum-event-directory.blade.php'),
        ],
        'forum' => [
            resource_path('views/forum/index.blade.php'),
            resource_path('views/components/forum-category-navigator.blade.php'),
            resource_path('views/components/forum-topic-card.blade.php'),
        ],
        'discover' => [
            resource_path('views/discover/index.blade.php'),
            resource_path('views/components/discovery-category-nav.blade.php'),
            resource_path('views/components/discovery-toolbar.blade.php'),
            resource_path('views/components/discovery-section.blade.php'),
            resource_path('views/components/discovery-result-card.blade.php'),
        ],
    ];

    foreach ($surfaces as $surface => $files) {
        $keys = collect($files)
            ->flatMap(function (string $path): array {
                preg_match_all(
                    "/(?:__|trans_choice)\\(\\s*'([^']+)'/",
                    File::get($path),
                    $matches,
                );

                return $matches[1];
            })
            ->unique()
            ->values();

        expect($keys, $surface)->not->toBeEmpty();

        foreach ($keys as $key) {
            $english = trans($key, locale: 'en');

            foreach (['lt', 'ru'] as $locale) {
                expect(trans($key, locale: $locale), "{$surface}:{$locale}:{$key}")
                    ->not->toBe($key)
                    ->not->toBe($english)
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

test('every detail and workspace route has a completed exception disposition', function () {
    /** @var array<string, list<string>> $classifications */
    $classifications = require base_path('tests/Support/page-identity-route-classification.php');
    $audit = Str::after(
        File::get(base_path('docs/portal/route-matrix.md')),
        '## Completed Detail And Workspace Exception Audit',
    );

    foreach (array_merge($classifications['detail'], $classifications['workspace']) as $routeName) {
        expect($audit, $routeName)->toContain("`{$routeName}`");
    }
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

test('representative detail report textareas retain visible programmatic labels', function () {
    $expert = ExpertProfile::factory()->create();
    $listing = Listing::factory()->create();

    foreach ([
        'expert detail' => route('experts.show', $expert),
        'marketplace detail' => route('marketplace.show', $listing),
    ] as $surface => $url) {
        $xpath = responseXPath($this->get($url)->assertOk());
        $details = $xpath->query('//main//textarea[@name="details"]');

        expect($details->length, $surface)->toBeGreaterThan(0);

        foreach ($details as $textarea) {
            expect($xpath->query('ancestor::label[normalize-space()]', $textarea)->length, $surface)
                ->toBe(1);
        }
    }
});

test('representative detail report labels and guidance are localized', function () {
    foreach ([
        'ui.details',
        'ui.describe_the_specific_concern',
        'ui.describe_the_specific_issue',
        'ui.serial_not_recorded',
        'ui.serial_number',
    ] as $key) {
        $english = trans($key, locale: 'en');

        foreach (['lt', 'ru'] as $locale) {
            expect(trans($key, locale: $locale), "{$locale}:{$key}")
                ->not->toBe($key)
                ->not->toBe($english)
                ->not->toBe('');
        }
    }
});

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
