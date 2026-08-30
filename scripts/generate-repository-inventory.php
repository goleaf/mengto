<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);

/** @return list<string> */
function inventoryFiles(string $root, string $directory, ?array $extensions = null): array
{
    $absolute = $root.'/'.$directory;

    if (! is_dir($absolute)) {
        return [];
    }

    $paths = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absolute, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || ! $file->isFile()) {
            continue;
        }

        if ($extensions !== null && ! in_array(strtolower($file->getExtension()), $extensions, true)) {
            continue;
        }

        $paths[] = str_replace($root.'/', '', $file->getPathname());
    }

    sort($paths);

    return $paths;
}

/** @param list<string> $paths */
function inventorySection(string $title, array $paths): string
{
    $output = "<details>\n<summary>{$title} (".count($paths).")</summary>\n\n";

    foreach ($paths as $path) {
        $output .= "- `{$path}`\n";
    }

    return $output."\n</details>\n\n";
}

/** @return list<string> */
function withoutPrefix(array $paths, string $prefix): array
{
    return array_values(array_filter(
        $paths,
        static fn (string $path): bool => ! str_starts_with($path, $prefix),
    ));
}

/** @return list<string> */
function firstPartyMarkdownFiles(string $root): array
{
    $paths = [];
    $finder = Finder::create()
        ->files()
        ->in($root)
        ->name('*.md')
        ->ignoreDotFiles(false)
        ->exclude([
            '.git', '.playwright-cli',
            'bootstrap/cache', 'node_modules', 'storage', 'vendor',
        ]);

    foreach ($finder as $file) {
        $paths[] = $file->getRelativePathname();
    }

    sort($paths);

    return $paths;
}

/** @return array{string, string} */
function markdownAuthority(string $path, array $indexedStatuses): array
{
    if (in_array($path, ['AGENTS.md', 'PRODUCT.md', 'DESIGN.md', 'SECURITY.md', 'CHANGELOG.md'], true)) {
        return ['Canonical / living', 'Root repository contract or source of truth'];
    }

    if (in_array($path, ['README.md', 'CLAUDE.md'], true)) {
        return ['Supporting', 'Repository entry point or contributor adapter'];
    }

    if (str_starts_with($path, '.agents/') || str_starts_with($path, '.claude/') || str_starts_with($path, '.cursor/')) {
        return ['Tooling mirror', 'Repository-local agent guidance subordinate to `AGENTS.md`'];
    }

    if ($path === 'docs/index.md') {
        return ['Canonical / living', 'Documentation source-of-truth index required by `AGENTS.md`'];
    }

    if (str_starts_with($path, 'docs/superpowers/plans/') || str_starts_with($path, 'docs/superpowers/specs/')) {
        return ['Historical / scoped evidence', 'Subordinate specification or delivery history'];
    }

    if ($path === 'docs/events.md') {
        return ['Historical / compatibility pointer', 'Superseded by `docs/events/index.md`'];
    }

    if (str_starts_with($path, 'docs/audits/')) {
        return ['Supporting evidence', 'Dated or living audit evidence; canonical documents retain authority'];
    }

    if (str_starts_with($path, 'docs/plans/')) {
        return ['Supporting scoped plan', 'Scoped plan subordinate to `docs/implementation-plan.md`'];
    }

    if (str_starts_with($path, 'docs/traceability/') || str_starts_with($path, 'docs/requirements/generated/')) {
        return ['Generated / supporting evidence', 'Generated traceability output'];
    }

    if (str_starts_with($path, 'docs/decisions/')) {
        return ['Supporting decision record', 'Decision evidence subordinate to current canonical requirements'];
    }

    if (str_starts_with($path, 'docs/requirements/')) {
        return ['Canonical requirement or generated evidence', 'Authority resolved by `docs/requirements.md` and repository precedence'];
    }

    if (isset($indexedStatuses[$path])) {
        $status = $indexedStatuses[$path];

        if (str_contains(strtolower($status), 'generated')) {
            return ['Canonical generated evidence', 'Registered by `docs/index.md`: '.$status];
        }

        if (str_contains(strtolower($status), 'superseded') || str_contains(strtolower($status), 'historical')) {
            return ['Historical / superseded', 'Registered by `docs/index.md`: '.$status];
        }

        return ['Canonical / living', 'Registered by `docs/index.md`: '.$status];
    }

    return ['Supporting', 'Unregistered first-party Markdown; not an independent source of authority'];
}

$routeProcess = new Process([PHP_BINARY, 'artisan', 'route:list', '--json'], $root);
$routeProcess->setTimeout(30);
$routeProcess->mustRun();
$routes = json_decode($routeProcess->getOutput(), true, flags: JSON_THROW_ON_ERROR);
$routes = array_values(array_filter(
    $routes,
    static fn (array $route): bool => ! str_starts_with((string) ($route['name'] ?? ''), 'boost.'),
));

foreach ($routes as &$route) {
    if (str_starts_with((string) ($route['name'] ?? ''), 'generated::')) {
        $route['name'] = null;
    }
}

unset($route);

usort($routes, static fn (array $left, array $right): int => [
    $left['uri'], $left['method'], $left['name'] ?? '',
] <=> [
    $right['uri'], $right['method'], $right['name'] ?? '',
]);

$livewire = inventoryFiles($root, 'app/Livewire', ['php']);
$livewireForms = array_values(array_filter(
    $livewire,
    static fn (string $path): bool => str_starts_with($path, 'app/Livewire/Forms/'),
));
$livewireComponents = withoutPrefix($livewire, 'app/Livewire/Forms/');
$bladeViews = inventoryFiles($root, 'resources/views', ['php']);
$bladeComponents = array_values(array_filter(
    $bladeViews,
    static fn (string $path): bool => str_starts_with($path, 'resources/views/components/'),
));
$migrations = inventoryFiles($root, 'database/migrations', ['php']);
$tests = inventoryFiles($root, 'tests', ['php']);
$browserTests = array_values(array_filter(
    inventoryFiles($root, 'scripts', ['mjs']),
    static fn (string $path): bool => str_contains($path, 'browser-check'),
));
$markdownFiles = firstPartyMarkdownFiles($root);
$indexedStatuses = [];
$documentationIndex = file_get_contents($root.'/docs/index.md');

if (is_string($documentationIndex)) {
    preg_match_all('/^\| `([^`]+\.md)` \|[^\n]*\| ([^|\n]+) \|$/m', $documentationIndex, $indexRows, PREG_SET_ORDER);

    foreach ($indexRows as $row) {
        $indexedStatuses[$row[1]] = trim($row[2]);
    }
}

$tables = [];

foreach ($migrations as $migration) {
    $source = file_get_contents($root.'/'.$migration);

    if (! is_string($source)) {
        continue;
    }

    preg_match_all('/Schema::create\(\s*[\'\"]([^\'\"]+)[\'\"]/', $source, $matches);

    foreach ($matches[1] as $table) {
        $tables[$table] = $migration;
    }
}

ksort($tables);

$roles = [];

foreach (inventoryFiles($root, 'app/Enums', ['php']) as $path) {
    $name = pathinfo($path, PATHINFO_FILENAME);

    if (! str_ends_with($name, 'Role') && $name !== 'UserStatus') {
        continue;
    }

    $class = 'App\\Enums\\'.$name;

    if (! enum_exists($class)) {
        continue;
    }

    $roles[$path] = array_map(
        static function (UnitEnum $case): string {
            $value = $case instanceof BackedEnum ? (string) $case->value : $case->name;

            return $case->name.'=`'.$value.'`';
        },
        $class::cases(),
    );
}

$cacheConsumers = [];
$integrationConsumers = [];

foreach (array_merge(
    inventoryFiles($root, 'app', ['php']),
    inventoryFiles($root, 'routes', ['php']),
) as $path) {
    $source = file_get_contents($root.'/'.$path);

    if (! is_string($source)) {
        continue;
    }

    if (preg_match('/(?:Cache::|->remember\(|->rememberForever\(|->lock\()/', $source) === 1) {
        $cacheConsumers[] = $path;
    }

    if (preg_match('/(?:Http::|Illuminate\\\\Http\\\\Client|GuzzleHttp|curl_)/', $source) === 1) {
        $integrationConsumers[] = $path;
    }
}

sort($cacheConsumers);
sort($integrationConsumers);

$output = <<<'MARKDOWN'
# Repository Inventory And Critical Workflow Chains

Generated by `php scripts/generate-repository-inventory.php` from the
first-party tree and Laravel route discovery. Do not edit this file manually.
The architecture suite requires byte parity with a fresh generator run.

Snapshot date: 2026-08-30.

## Runtime And Integration Topology

- Web entry: `public/index.php` -> `bootstrap/app.php` -> `routes/web.php`.
- Console entry: `artisan` -> `bootstrap/app.php` -> `routes/console.php`.
- Queue connection defaults to synchronous execution for application
  correctness; there are no first-party Job, Event, Listener, or Notification
  classes and no scheduled tasks.
- No first-party outbound HTTP client or webhook handler is present. Provider
  selection remains outside the implemented runtime.
- Private/local file delivery is owned by `PrivateFileResponse` and
  authenticated portal media by `PortalMediaResponse`.
- npm with `package-lock.json` is the only frontend package-manager boundary.

## Exact Critical Workflow Chains

| Workflow | Current entry-to-response chain | Primary verification |
| --- | --- | --- |
| Registration | `GET /register` -> portal guest allowlist -> `App\Livewire\Auth\Register` -> `RegistrationForm` rules -> `RegisterUser` -> user transaction/server actor key -> framework `Registered` event + login/session regeneration -> portal redirect | `tests/Feature/Auth/AuthenticationTest.php`, `PortalAccessBoundaryTest.php` |
| Pet creation | `/compose/pet` -> portal/auth/active/verified middleware -> `App\Livewire\Pets\CreatePetProfile` + form objects -> policy checks -> `App\Actions\CreatePetProfile` -> idempotent transaction/locks -> profile, manager, privacy, alias, lifecycle and audit rows; protected photo compensation -> redirect/render | Pet profile foundation/workspace/duplicate-access tests |
| Social mutation | `POST /actions` -> portal/auth/active/verified + throttle -> `PerformActionRequest` -> `PerformActionController` -> `PerformAction` authorization -> locked/versioned encrypted `UserDomainState` mutation + audit -> localized redirect/JSON | `tests/Feature/SocialPersistenceTest.php`, auth policy tests |
| Marketplace acceptance | `POST /marketplace/{listing}/actions` -> portal/auth/active/verified + throttle/binding -> `PerformListingActionRequest` decimal validation -> listing policy -> `PerformListingAction::acceptRequest` transaction/row locks -> `CreateOrder` exact minor-unit total and schema-width guard -> reservation/listing/order/audit persistence -> localized redirect | `tests/Feature/MarketplaceFlowTest.php`, `MinorUnitAmountTest.php` |
| Medical/care grant use | `/medical-access/{token}` or `/care-access/{token}` -> portal authentication + private response middleware + throttle -> `ResolveMedicalAccess`/`ResolveCareAccess` hashed-token lock, expiry/revocation/view and account binding -> downstream section/permission/child/file checks inside `execute` for downloads -> successful counter and actual-bearer audit transaction -> protected view/stream; shared care entry audit is committed with `CreateCareEntry` | `MedicalRecordTest.php`, `CareJournalTest.php`, private-file/security tests |
| Device command/access | `/devices/{device}/commands` -> portal/auth/active/verified/password-confirm/throttle -> `StoreDeviceCommandRequest` -> `SmartDevicePolicy::controlCommand` -> `IssueDeviceCommand` idempotent locked transaction/state-event-audit -> redirect; `/device-access/{token}` uses hashed-token lock/account binding/view counter/actual-bearer audit -> privacy-safe dashboard | `SmartDeviceTest.php`, `AuthorizationTest.php`, `PolicyMatrixTest.php` |
| Forum topic lifecycle | lifecycle Livewire/HTTP entry -> portal/auth/active/verified -> request/form validation -> `ForumTopicPolicy` explicit owner or `moderateLifecycle` administrator ability -> `ChangeForumTopicState` -> `ForumTopicLifecycle` lock/optimistic version/category/legal-hold validation + immutable event -> localized component/redirect response | `ForumTopicLifecycleTest.php`, forum policy/architecture tests |

## Route Inventory

MARKDOWN;

$output .= '| Method | URI | Name | Action | Middleware |'."\n";
$output .= '| --- | --- | --- | --- | --- |'."\n";

foreach ($routes as $route) {
    $cells = [
        $route['method'],
        $route['uri'],
        $route['name'] ?? '-',
        $route['action'],
        implode(', ', $route['middleware'] ?? []),
    ];
    $cells = array_map(
        static fn (string $cell): string => str_replace('|', '\\|', $cell),
        $cells,
    );
    $output .= '| '.implode(' | ', array_map(static fn (string $cell): string => "`{$cell}`", $cells))." |\n";
}

$output .= "\n## Role And Account-State Inventory\n\n";
$output .= "`users.is_admin` is the platform-administrator flag and is effective only for an active account. Scoped domain roles are:\n\n";

foreach ($roles as $path => $cases) {
    $output .= "- `{$path}`: ".implode(', ', $cases).".\n";
}

$output .= "\n## Persistence Table Inventory\n\n";
$output .= 'The migration ledger creates '.count($tables)." named application/framework tables; the isolated database smoke additionally reports framework-managed runtime tables where applicable.\n\n";

foreach ($tables as $table => $migration) {
    $output .= "- `{$table}` — `{$migration}`\n";
}

$output .= "\n## Cache And External-Client Inventory\n\n";
$output .= inventorySection('First-party cache or atomic-lock consumers', $cacheConsumers);
$output .= inventorySection('First-party outbound HTTP/client consumers', $integrationConsumers);

$output .= "## Complete First-Party Markdown Authority Inventory\n\n";
$output .= 'This table classifies every first-party Markdown file outside dependency, runtime, and browser-artifact trees. Repository-local agent and editor guidance is included as a tooling mirror and remains subordinate to `AGENTS.md`. Governing precedence remains `AGENTS.md` -> nested instructions -> canonical requirements -> security/privacy/data integrity -> architecture decisions -> canonical plan -> accurate tests -> code -> historical evidence.'."\n\n";
$output .= '| Path | Authority classification | Evidence / precedence note |'."\n";
$output .= '| --- | --- | --- |'."\n";

foreach ($markdownFiles as $path) {
    [$authority, $evidence] = markdownAuthority($path, $indexedStatuses);
    $output .= "| `{$path}` | {$authority} | {$evidence} |\n";
}

$output .= "\n";

$sections = [
    'Runtime manifests and entry points' => array_values(array_filter([
        'artisan', 'composer.json', 'composer.lock', 'package.json',
        'package-lock.json', 'phpunit.xml', 'phpstan.neon', 'vite.config.js',
        'public/index.php',
    ], static fn (string $path): bool => file_exists($root.'/'.$path))),
    'Route definitions' => inventoryFiles($root, 'routes', ['php']),
    'Framework bootstrap files' => withoutPrefix(
        inventoryFiles($root, 'bootstrap', ['php']),
        'bootstrap/cache/',
    ),
    'Configuration files' => inventoryFiles($root, 'config', ['php']),
    'Application providers' => inventoryFiles($root, 'app/Providers', ['php']),
    'Console commands' => inventoryFiles($root, 'app/Console/Commands', ['php']),
    'Enums' => inventoryFiles($root, 'app/Enums', ['php']),
    'Events' => inventoryFiles($root, 'app/Events', ['php']),
    'Jobs' => inventoryFiles($root, 'app/Jobs', ['php']),
    'Listeners' => inventoryFiles($root, 'app/Listeners', ['php']),
    'Notifications' => inventoryFiles($root, 'app/Notifications', ['php']),
    'HTTP middleware' => inventoryFiles($root, 'app/Http/Middleware', ['php']),
    'HTTP controllers' => inventoryFiles($root, 'app/Http/Controllers', ['php']),
    'Form Requests' => inventoryFiles($root, 'app/Http/Requests', ['php']),
    'API Resources' => inventoryFiles($root, 'app/Http/Resources', ['php']),
    'Actions' => inventoryFiles($root, 'app/Actions', ['php']),
    'Services' => inventoryFiles($root, 'app/Services', ['php']),
    'Models' => inventoryFiles($root, 'app/Models', ['php']),
    'Policies' => inventoryFiles($root, 'app/Policies', ['php']),
    'Validation rules' => inventoryFiles($root, 'app/Rules', ['php']),
    'Livewire components' => $livewireComponents,
    'Livewire form objects' => $livewireForms,
    'Blade views' => $bladeViews,
    'Anonymous Blade components' => $bladeComponents,
    'Migrations' => $migrations,
    'Factories' => inventoryFiles($root, 'database/factories', ['php']),
    'Seeders' => inventoryFiles($root, 'database/seeders', ['php']),
    'PHP tests and support' => $tests,
    'Browser test runners' => $browserTests,
    'Runtime, generation, and verification scripts' => array_merge(
        inventoryFiles($root, 'scripts', ['php']),
        inventoryFiles($root, 'scripts', ['mjs']),
    ),
    'CI workflow files' => array_merge(
        inventoryFiles($root, '.github/workflows', ['yml', 'yaml']),
        inventoryFiles($root, '.gitlab', ['yml', 'yaml']),
    ),
    'Resource JavaScript modules' => inventoryFiles($root, 'resources/js', ['js']),
    'CSS and SCSS sources' => array_merge(
        inventoryFiles($root, 'resources/css', ['css']),
        inventoryFiles($root, 'resources/scss', ['scss']),
    ),
    'Translation catalogues' => inventoryFiles($root, 'lang', ['php']),
];

$output .= "\n## Complete First-Party Symbol And File Inventories\n\n";

foreach ($sections as $title => $paths) {
    sort($paths);
    $output .= inventorySection($title, $paths);
}

$output = rtrim($output)."\n";
$outputPath = $root.'/docs/audits/repository-inventory.md';
$arguments = array_slice($_SERVER['argv'] ?? [], 1);

if (in_array('--write', $arguments, true)) {
    if (file_put_contents($outputPath, $output) === false) {
        fwrite(STDERR, "Unable to write {$outputPath}.\n");
        exit(1);
    }

    fwrite(STDOUT, "Generated docs/audits/repository-inventory.md.\n");
    exit(0);
}

if (in_array('--check', $arguments, true)) {
    $current = is_file($outputPath) ? file_get_contents($outputPath) : false;

    if (! is_string($current) || ! hash_equals($output, $current)) {
        fwrite(STDERR, "docs/audits/repository-inventory.md is stale.\n");
        exit(1);
    }

    fwrite(STDOUT, "docs/audits/repository-inventory.md is current.\n");
    exit(0);
}

fwrite(STDOUT, $output);
