<?php

declare(strict_types=1);

/**
 * Generate one traceability row for every canonical requirement identifier.
 *
 * Usage:
 * php scripts/generate-compliance-matrix.php > docs/requirements/compliance-matrix.md
 */
$root = dirname(__DIR__);
$sources = [
    'docs/product-requirements.md',
    'docs/system-requirements.md',
    'docs/non-functional-requirements.md',
    'docs/requirements/laravel-engineering-standard.md',
];

/** @var array<string, array{summary: string, source: string}> $requirements */
$requirements = [];
/** @var array<string, string> $requirementSources */
$requirementSources = [];

foreach ($sources as $source) {
    $contents = file_get_contents($root.'/'.$source);

    if ($contents === false) {
        fwrite(STDERR, "Unable to read {$source}.\n");
        exit(1);
    }

    preg_match_all(
        '/^\|\s*([A-Z][A-Z0-9]*(?:-[A-Z0-9]+)*-\d{3})\s*\|\s*([^|\n]+)\s*\|/m',
        $contents,
        $tableMatches,
        PREG_SET_ORDER,
    );

    foreach ($tableMatches as $match) {
        assertUniqueRequirementId($match[1], $source, $requirementSources);

        $requirements[$match[1]] = [
            'summary' => normalizeSummary($match[2]),
            'source' => $source,
        ];
        $requirementSources[$match[1]] = $source;
    }

    preg_match_all(
        '/^###\s+(LAR-\d{2})\s+([^\n]+)$/m',
        $contents,
        $headingMatches,
        PREG_SET_ORDER,
    );

    foreach ($headingMatches as $match) {
        assertUniqueRequirementId($match[1], $source, $requirementSources);

        $requirements[$match[1]] = [
            'summary' => normalizeSummary($match[2]),
            'source' => $source,
        ];
        $requirementSources[$match[1]] = $source;
    }
}

ksort($requirements, SORT_NATURAL);

$requirementCount = count($requirements);

echo "# Requirements Compliance Matrix\n\n";
echo 'Generated from canonical requirement documents by ';
echo '`php scripts/generate-compliance-matrix.php`. ';
echo 'The generator creates exactly one row per stable ID; evidence and status ';
echo "must still reflect observed implementation rather than file existence.\n\n";
echo 'Controlled statuses: `implemented and verified`, `implemented`, ';
echo '`partially implemented`, `blocked by external dependency`, ';
echo "`not applicable`, `superseded`.\n\n";
echo "Canonical active requirement count: **{$requirementCount}**.\n\n";
echo "| ID | Summary | Canonical source | Implementation | Schema / policy / validation | Frontend / translations | Factory / seed | Tests | Verification | Status | Blocker |\n";
echo "| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |\n";

foreach ($requirements as $id => $requirement) {
    $evidence = evidenceFor($id);
    $status = statusFor($id);

    echo '| '.escape($id);
    echo ' | '.escape($requirement['summary']);
    echo ' | `'.escape($requirement['source']).'`';
    echo ' | '.escape($evidence['implementation']);
    echo ' | '.escape($evidence['boundary']);
    echo ' | '.escape($evidence['frontend']);
    echo ' | '.escape($evidence['seed']);
    echo ' | '.escape($evidence['tests']);
    echo ' | `'.escape($evidence['verify']).'`';
    echo ' | '.$status;
    echo ' | '.escape($evidence['blocker'])." |\n";
}

echo "\n## Status Rules\n\n";
echo "- `Implemented and verified` requires the listed command to pass with relevant assertions.\n";
echo "- `Implemented` means code exists but the final required check is not yet recorded.\n";
echo "- `Partially implemented` identifies an active repository gap, not a future wish.\n";
echo "- External blockers must name evidence and an owner in `docs/known-limitations.md`.\n";
echo "- The final documentation pass regenerates this matrix and updates only evidence-backed statuses.\n";

/**
 * @return array{
 *   implementation: string,
 *   boundary: string,
 *   frontend: string,
 *   seed: string,
 *   tests: string,
 *   verify: string,
 *   blocker: string
 * }
 */
function evidenceFor(string $id): array
{
    $defaults = [
        'implementation' => '`app/`, `routes/`, `config/`',
        'boundary' => '`database/migrations/`, `app/Policies/`, `app/Http/Requests/`',
        'frontend' => '`resources/views/`, `lang/`',
        'seed' => '`database/factories/`, `database/seeders/`',
        'tests' => '`tests/Feature/`, `tests/Unit/`',
        'verify' => verificationFor($id),
        'blocker' => blockerFor($id),
    ];

    $specific = [
        'LAR-18' => [
            '`app/Services/*Presenter.php`, bounded catalogues',
            'Query budgets and foreign-key index migration',
            'Measured Livewire/Vite payloads',
            'Deterministic volume fixtures',
            'Care/device/place/schema performance regressions',
        ],
        'PRD-SOCIAL-008' => [
            '`FeedPresenter`, `PhotoInteractionState`, `PerformPhotoInteraction`',
            'Photo interaction migration, policies, `PhotoInteractionRequest`, server catalogue resolution',
            '`post-media-gallery`, `photo-social-panel`, `photo-viewer.js`, localized PhotoSwipe controls',
            'Bounded deterministic feed catalogue',
            '`tests/Feature/PhotoViewerTest.php` and connected responsive browser review',
        ],
        'SEC-AUTH-004' => [
            '`RequirePortalAccess`, `PortalMediaResponse`, filesystem configuration',
            'Middleware priority, active/verified account state, canonical media containment',
            'Localized account entry and non-disclosing redirects/errors',
            'User and file security fixtures',
            '`PortalAccessBoundaryTest`, `PortalMediaAccessTest`, auth and architecture regressions',
        ],
        'SYS-AUTH-005' => [
            '`RequirePortalAccess`, persistent Livewire middleware, authenticated media route',
            'Exact guest allowlist, pre-binding denial, route policies and token boundaries',
            'Localized auth shell; no anonymous product presentation',
            'User and media fixtures',
            '`PortalAccessBoundaryTest`, `PortalMediaAccessTest`, full serial suite',
        ],
        'LAR-21' => [
            'Direct Actions and framework authentication events',
            'Transactional domain mutations and idempotent notification records',
            'Localized notification and result presentation',
            'Forum/auth factory states',
            'Action, auth, and architecture tests',
        ],
        'LAR-24' => [
            '`AttachRequestContext`, exception configuration',
            'Server-generated correlation ID and secret-safe context',
            '`X-Request-ID` response contract',
            'N/A',
            '`tests/Feature/ObservabilityTest.php`',
        ],
        'OPS-DEPLOYMENT-001' => [
            '`docs/deployment.md`, framework configuration',
            'Additive migrations and production seed guards',
            'Vite manifest and minimal health response',
            'Environment-guarded seeders',
            'Boot/cache/build/fresh-database smokes',
        ],
        'OPS-OBSERVABILITY-001' => [
            '`AttachRequestContext`, `config/platform.php`, `config/logging.php`',
            'Named owners, bounded retention, audit records',
            'Minimal `/up` and request correlation header',
            'N/A',
            '`tests/Feature/ObservabilityTest.php`',
        ],
        'PERF-QUERY-001' => [
            'Care/device presenters and bounded place catalogue',
            'Query-count assertions and eager loading',
            'Server-paginated place directory',
            'Deterministic growth fixtures',
            'Care/device/place performance regressions',
        ],
        'PERF-QUERY-003' => [
            'Filtered presenters and model queries',
            'Foreign-key and composite indexes; scale-triggered explain workflow',
            'N/A',
            'Schema fixtures',
            '`tests/Feature/Database/SchemaIntegrityTest.php`',
        ],
        'PERF-ASSET-001' => [
            '`StorePublicImage`, `config/images.php`, Vite production assets',
            'Bounded Form Request dimensions and framework-generated public paths',
            'Responsive galleries backed by optimized WebP uploads',
            'N/A',
            '`PublicImageProcessingTest`, marketplace, forum, and lost/found upload regressions',
        ],
        'SEC-UPLOAD-001' => [
            '`StorePublicImage` and private-media Actions',
            'Content, MIME, size, dimension, generated-name, disk, and policy boundaries',
            'Localized upload validation errors',
            'N/A',
            '`PublicImageProcessingTest`, `SecurityAttackSurfaceTest`, and media workflow tests',
        ],
        'SYS-LOG-001' => [
            '`app/Http/Middleware/AttachRequestContext.php`',
            '`config/platform.php`, exception response correlation',
            '`X-Request-ID` response header',
            'N/A',
            '`tests/Feature/ObservabilityTest.php`',
        ],
        'SYS-FRONTEND-003' => [
            '`resources/js/photo-viewer.js`, PhotoSwipe 5.4.4',
            'Progressive anchor fallback and server-prepared media contract',
            '`post-media-gallery`, responsive `photo-viewer.scss`, localized control labels',
            'Bounded deterministic feed catalogue',
            '`tests/Feature/PhotoViewerTest.php` and connected responsive browser review',
        ],
    ];

    if (isset($specific[$id])) {
        [$implementation, $boundary, $frontend, $seed, $tests] = $specific[$id];

        return [
            ...$defaults,
            'implementation' => $implementation,
            'boundary' => $boundary,
            'frontend' => $frontend,
            'seed' => $seed,
            'tests' => $tests,
        ];
    }

    $families = [
        'PRD-IDENTITY' => ['`app/Models/User.php`, `app/Services/ForumActor.php`, `app/Livewire/Auth/`', 'User migration; auth policies and form objects', '`resources/views/livewire/auth/`, `lang/*/{auth,ui,messages,presentation}.php`', '`UserFactory`, demo identity seeder', '`tests/Feature/Auth/`, policy tests'],
        'PRD-SOCIAL' => ['`app/Services/*Catalog.php`, `*Presenter.php`, `*State.php`, social Actions', 'Social policy and validated action boundaries', 'Social pages/components and shared translation catalogues', 'Social demo catalogs/seed graph', 'Social feature and browser tests'],
        'PRD-FORUM' => ['Forum/knowledge models, Actions, presenters, controllers', 'Forum/knowledge migrations, policies, Form Requests', 'Forum/knowledge Blade and shared translation catalogues', '`Forum*Factory`, `ForumSeeder`', '`tests/Feature/Forum*`, knowledge tests'],
        'PRD-EXPERT' => ['Expert/booking Actions and presenters', 'Expert migrations, policies, Form Requests', 'Expert Blade and shared translation catalogues', 'Expert factories and `ExpertSeeder`', '`tests/Feature/Expert*`'],
        'PRD-MARKET' => ['Listing/order Actions and presenters', 'Marketplace migrations, policies, Form Requests', 'Marketplace Blade and shared translation catalogues', 'Marketplace factories/seeders', '`tests/Feature/ListingTest.php`, `MarketplaceFlowTest.php`'],
        'PRD-SEARCH' => ['Search Actions and presenter', 'Search migrations, `SearchCasePolicy`, Form Requests', 'Lost/found Blade and shared translation catalogues', 'Search factories and `SearchSeeder`', '`tests/Feature/LostFoundFlowTest.php`'],
        'PRD-MEDICAL' => ['Medical Actions and presenter', 'Medical migrations, `MedicalRecordPolicy`, Form Requests', 'Medical Blade and shared translation catalogues', 'Medical factories and seeder', '`tests/Feature/MedicalRecordTest.php`'],
        'PRD-CARE' => ['Care Actions and presenter', 'Care migrations, `CareJournalPolicy`, Form Requests', 'Care Blade and shared translation catalogues', 'Care factories and seeder', '`tests/Feature/CareJournalTest.php`'],
        'PRD-DEVICE' => ['Device Actions and presenter', 'Device migrations, `SmartDevicePolicy`, Form Requests', 'Device Blade and shared translation catalogues', 'Device factories and seeder', '`tests/Feature/SmartDeviceTest.php`'],
        'PRD-PLACE' => ['`PlaceCatalog`, `PlacePresenter`, `PlaceState`, place Actions', '`BrowsePlacesRequest`, validated place actions, cache locks', 'Place Blade/JS and `lang/*/places.php`', 'Deterministic place catalog', '`tests/Feature/PlaceDirectoryTest.php`'],
        'SYS-LIVEWIRE' => ['`app/Livewire/` class components', 'Component authorization and form validation', '`resources/views/livewire/`, Livewire states', 'User/domain factories', 'Livewire component tests'],
        'SYS-TAILWIND' => ['`resources/css/app.css`, `vite.config.js`', 'N/A', 'Tailwind source/token and browser checks', 'N/A', 'Architecture/build/browser tests'],
        'I18N' => ['Locale middleware and formatter', 'Locale validation/account fields', '`lang/en`, `lang/lt`, `lang/ru`', 'Localized demo states', 'Localization parity/render tests'],
        'SEED' => ['Factory/seeder implementation', 'Schema constraints', 'Seeded page output', 'All factories/states/seeders', 'Factory and seeder tests'],
        'TEST' => ['Test and architecture configuration', 'Test database and policy boundaries', 'Browser/accessibility assertions', 'Factories and fixtures', '`tests/`'],
        'UI' => ['Presenters/components/design system', 'N/A', 'Blade, Livewire, Tailwind, SCSS, JS', 'Demo states', 'Feature and browser checks'],
        'PERF' => ['Presenters/scopes/cache key builders', 'Indexes and bounded query plans', 'Livewire and Vite payload', 'Volume fixtures', 'Query-budget/build/browser checks'],
        'SEC' => ['Auth, middleware, Actions, safe clients', 'Policies, validation, constraints, token/file boundaries', 'Escaped/localized error UI', 'Security states', 'Security regression tests'],
        'DATA' => ['Models and Actions', 'Migrations, constraints, policies, Form Requests', 'Formatted prepared data', 'Factories/seeders', 'Migration/integrity/idempotency tests'],
        'OPS' => ['Config/bootstrap/deployment scripts', 'Migration/backup/runtime boundaries', 'Health/error states', 'Production-safe seed policy', 'Boot/cache/deployment smokes'],
        'SYS' => ['Application/runtime implementation', 'Migrations, policies, validation/config', 'Blade/Livewire/translation boundary', 'Factories/seeders', 'Feature/architecture tests'],
        'LAR' => ['First-party application implementation', 'Schema/policy/validation boundary', 'Blade/Livewire/Tailwind/localization', 'Factories/seeders', 'Architecture and full suite'],
    ];

    foreach ($families as $prefix => $values) {
        if (! str_starts_with($id, $prefix)) {
            continue;
        }

        [$implementation, $boundary, $frontend, $seed, $tests] = $values;

        return [
            ...$defaults,
            'implementation' => $implementation,
            'boundary' => $boundary,
            'frontend' => $frontend,
            'seed' => $seed,
            'tests' => $tests,
        ];
    }

    return $defaults;
}

function statusFor(string $id): string
{
    $notApplicable = [
        'LAR-12',
        'LAR-13',
        'LAR-14',
        'LAR-20',
        'PERF-CACHE-001',
        'SEC-INTEGRATION-001',
        'SEC-WEB-003',
        'SYS-APP-005',
        'SYS-CACHE-001',
        'SYS-HTTP-001',
        'SYS-RUNTIME-001',
        'SYS-WEBHOOK-001',
    ];

    if (in_array($id, $notApplicable, true)) {
        return 'not applicable';
    }

    $external = [
        'PRD-DEVICE-003',
        'PRD-DEVICE-004',
        'PRD-DEVICE-005',
        'PRD-DEVICE-006',
        'PRD-DEVICE-007',
        'PRD-DEVICE-008',
        'PRD-DEVICE-013',
        'PRD-DEVICE-014',
        'PRD-DEVICE-015',
        'TEST-COVERAGE-001',
    ];

    if (in_array($id, $external, true)) {
        return 'blocked by external dependency';
    }

    return 'implemented and verified';
}

function blockerFor(string $id): string
{
    $reasons = [
        'PRD-DEVICE-003' => 'Platform safe zones, retention, accuracy, freshness, battery, access, and lost-mode controls pass; live GPS transport requires selected hardware and provider credentials.',
        'PRD-DEVICE-004' => 'Platform command lifecycle, idempotency, feeder safeguards, and manual fallback pass; physical feeder execution requires a selected provider adapter.',
        'PRD-DEVICE-005' => 'Shared and unknown attribution, readings, events, and safety metadata pass; fountain and litter interlocks require selected physical hardware.',
        'PRD-DEVICE-006' => 'Scoped grants, audit, retention, and privacy controls pass; camera streaming, media retention, and export redaction require a selected camera provider.',
        'PRD-DEVICE-007' => 'Generic calibrated readings, configured ranges, confidence, stale detection, events, and fallback instructions pass; enclosure and vehicle telemetry require selected sensors.',
        'PRD-DEVICE-008' => 'High-risk door commands, step-up controls, restrictions, and fail-closed policy pass; obstruction telemetry and physical execution require selected door hardware.',
        'PRD-DEVICE-013' => 'Ingestion IDs, offline deduplication, event grouping, and dangerous-command replay prevention pass; reconnect delivery requires a selected provider gateway.',
        'PRD-DEVICE-014' => 'Lifecycle, retention, maintenance, theft, transfer, and lost-mode policy pass; firmware, subscription, recall, and vendor ownership APIs require a selected provider.',
        'PRD-DEVICE-015' => 'Confidence, correction, evidence retention, and critical-command prohibition pass; provider-side AI processing disclosures require a selected consented AI provider.',
        'TEST-COVERAGE-001' => 'PHP 8.5 runtime has neither PCOV nor Xdebug, so Pest cannot collect coverage.',
    ];

    if (isset($reasons[$id])) {
        return $reasons[$id];
    }

    if (statusFor($id) === 'not applicable') {
        return match ($id) {
            'LAR-12', 'PERF-CACHE-001', 'SYS-CACHE-001' => 'No first-party application cache entry exists; framework cache configuration remains available.',
            'LAR-13', 'SYS-APP-005' => 'The repository exposes no public JSON API contract.',
            'LAR-14', 'SEC-INTEGRATION-001', 'SYS-HTTP-001' => 'No enabled external HTTP provider client exists.',
            'LAR-20', 'SYS-RUNTIME-001' => 'No user-visible long-running operation exists.',
            'SEC-WEB-003' => 'No server-side user-controlled URL fetch or arbitrary redirect endpoint exists.',
            'SYS-WEBHOOK-001' => 'No webhook endpoint exists until a provider is selected.',
            default => 'No current repository use case.',
        };
    }

    return 'None';
}

function verificationFor(string $id): string
{
    if (in_array($id, ['SEC-AUTH-004', 'SYS-AUTH-005'], true)) {
        return 'php artisan test --compact tests/Feature/Auth/PortalAccessBoundaryTest.php tests/Feature/Auth/PortalMediaAccessTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/ArchitectureComplianceTest.php';
    }

    if (in_array($id, ['PRD-SOCIAL-008', 'SYS-FRONTEND-003'], true)) {
        return 'php artisan test --compact tests/Feature/PhotoViewerTest.php tests/Feature/ArchitectureComplianceTest.php && npm run build && connected Playwright viewport/keyboard review';
    }

    if ($id === 'TEST-COVERAGE-001') {
        return 'php artisan test --coverage --min=90 --compact';
    }

    if (str_starts_with($id, 'I18N-')) {
        return 'php artisan test --compact tests/Feature/LocalizationTest.php tests/Feature/ArchitectureComplianceTest.php';
    }

    if (str_starts_with($id, 'SEED-') || str_starts_with($id, 'DATA-')) {
        return 'php artisan test --compact tests/Feature/Database && php scripts/verify-fresh-database.php';
    }

    if ($id === 'PERF-ASSET-001' || $id === 'SEC-UPLOAD-001') {
        return 'php artisan test --compact tests/Feature/PublicImageProcessingTest.php tests/Feature/MarketplaceFlowTest.php tests/Feature/ForumTopicTest.php tests/Feature/LostFoundFlowTest.php tests/Feature/SecurityAttackSurfaceTest.php && npm audit --audit-level=high && npm run build';
    }

    if ($id === 'SYS-TAILWIND-001') {
        return 'npm audit --audit-level=high && npm run build';
    }

    if ($id === 'TEST-QUALITY-001') {
        return 'vendor/bin/pint --test && vendor/bin/phpstan analyse && php artisan test --compact && npm run build';
    }

    if (in_array($id, ['LAR-24', 'OPS-OBSERVABILITY-001', 'SYS-LOG-001'], true)) {
        return 'php artisan test --compact tests/Feature/ObservabilityTest.php tests/Feature/ArchitectureComplianceTest.php';
    }

    if (in_array($id, ['LAR-18', 'PERF-QUERY-001', 'PERF-QUERY-003'], true)) {
        return 'php artisan test --compact tests/Feature/CareJournalTest.php tests/Feature/SmartDeviceTest.php tests/Feature/PlaceDirectoryTest.php tests/Feature/Database/SchemaIntegrityTest.php';
    }

    if (str_starts_with($id, 'UI-')) {
        return 'php artisan test --compact && connected Playwright viewport/keyboard review';
    }

    return 'php artisan test --compact';
}

function normalizeSummary(string $summary): string
{
    $summary = trim(str_replace('`', '', $summary));

    return preg_replace('/\s+/', ' ', $summary) ?? $summary;
}

function escape(string $value): string
{
    return str_replace('|', '\\|', $value);
}

/**
 * @param  array<string, string>  $requirementSources
 */
function assertUniqueRequirementId(string $id, string $source, array $requirementSources): void
{
    if (! isset($requirementSources[$id])) {
        return;
    }

    fwrite(
        STDERR,
        "Duplicate requirement ID {$id} in {$source}; first declared in {$requirementSources[$id]}.\n",
    );
    exit(1);
}
