<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route as RouteFacade;

function sourceFiles(string $path, string $extension = 'php'): array
{
    return collect(File::allFiles($path))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === $extension)
        ->values()
        ->all();
}

test('blade templates remain passive', function () {
    foreach (sourceFiles(resource_path('views'), 'php') as $file) {
        $contents = $file->getContents();

        expect($contents, $file->getRelativePathname())
            ->not->toContain('@php')
            ->not->toContain('@endphp')
            ->not->toContain('<?php')
            ->not->toContain('{!!')
            ->not->toMatch('/\bapp\s*\(/')
            ->not->toMatch('/\b(?:collect|str)\s*\(/')
            ->not->toContain('onclick=')
            ->not->toContain('<style');

        expect(
            preg_match('/\b[A-Z][A-Za-z0-9_\\\\]*::[A-Za-z_][A-Za-z0-9_]*\s*\(/', $contents),
            $file->getRelativePathname(),
        )->toBe(0);
    }
});

test('app shell views do not introduce nested main landmarks', function () {
    foreach (sourceFiles(resource_path('views'), 'php') as $file) {
        $contents = $file->getContents();

        if (! str_contains($contents, '<x-app-shell')) {
            continue;
        }

        expect(
            preg_match('/<main(?:\s|>)/', $contents),
            $file->getRelativePathname(),
        )->toBe(0);
    }
});

test('blade presentation literals are localized', function () {
    $result = Process::path(base_path())
        ->timeout(30)
        ->run([PHP_BINARY, 'scripts/localize-blade-literals.php', '--check']);

    expect($result->successful(), $result->errorOutput().$result->output())->toBeTrue();
});

test('blade interpolations do not contain untranslated sentence fragments', function () {
    foreach (sourceFiles(resource_path('views'), 'php') as $file) {
        $contents = $file->getContents();
        $attributeMatches = [];
        $textMatches = [];

        preg_match_all(
            '/\b(?:aria-label|title|placeholder|label|meta)="([^"]*\{\{.*?\}\}[^"]*)"/su',
            $contents,
            $attributeMatches,
        );
        preg_match_all('/(?<![=-])>([^<]*\{\{.*?\}\}[^<]*)</su', $contents, $textMatches);

        foreach ($attributeMatches[1] ?? [] as $attribute) {
            $staticText = preg_replace('/\{\{.*?\}\}/su', '', $attribute) ?? '';

            expect(
                preg_match('/\pL/u', $staticText),
                $file->getRelativePathname().': '.$attribute,
            )->toBe(0);
        }

        foreach ($textMatches[1] ?? [] as $text) {
            if (str_contains($text, '@')) {
                continue;
            }

            $staticText = preg_replace('/\{\{.*?\}\}/su', '', $text) ?? '';

            if (str_contains($staticText, 'data-') || str_contains($staticText, 'class')) {
                continue;
            }

            expect(
                preg_match('/\pL/u', $staticText),
                $file->getRelativePathname().': '.$text,
            )->toBe(0);
        }
    }
});

test('first party php messages are localized', function () {
    $result = Process::path(base_path())
        ->timeout(30)
        ->run([PHP_BINARY, 'scripts/localize-php-messages.php', '--check']);

    expect($result->successful(), $result->errorOutput().$result->output())->toBeTrue();
});

test('javascript receives user facing copy from localized markup', function () {
    $literalMutation = '/(?:textContent|innerText)\s*=\s*[\'"][^\'"]*\pL/iu';
    $literalAccessibleName = '/setAttribute\(\s*[\'"](?:aria-label|title)[\'"]\s*,\s*[\'"][^\'"]*\pL/iu';

    foreach (sourceFiles(resource_path('js'), 'js') as $file) {
        $contents = $file->getContents();

        expect(preg_match($literalMutation, $contents), $file->getRelativePathname())
            ->toBe(0)
            ->and(preg_match($literalAccessibleName, $contents), $file->getRelativePathname())
            ->toBe(0);
    }
});

test('forum accessibility architecture uses semantic tables and avoids drag only controls', function () {
    foreach (sourceFiles(resource_path('views/livewire/forum'), 'php') as $file) {
        $contents = $file->getContents();

        expect($contents, $file->getRelativePathname())
            ->not->toMatch('/\bdraggable\s*=/i')
            ->not->toContain('wire:sort')
            ->not->toContain('x-sort')
            ->not->toMatch('/\bon(?:drag|drop)[a-z]*\s*=/i');

        if (str_contains($contents, '<table')) {
            expect(substr_count($contents, '<caption'), $file->getRelativePathname())
                ->toBe(substr_count($contents, '<table'))
                ->and(substr_count($contents, 'scope="col"'), $file->getRelativePathname())
                ->toBeGreaterThanOrEqual(substr_count($contents, '<table'));
        }
    }

    foreach ([
        resource_path('views/forum'),
        resource_path('views/livewire/forum'),
    ] as $path) {
        foreach (sourceFiles($path, 'php') as $file) {
            expect($file->getContents(), $file->getRelativePathname())
                ->not->toMatch('/<(?!dialog\b)[a-z][^>]*\brole=["\']dialog["\']/i');
        }
    }
});

test('forum validation summaries and media adapters remain navigation safe', function () {
    $component = File::get(resource_path('views/components/forum-error-summary.blade.php'));
    $adapter = File::get(resource_path('js/forum-accessibility.js'));
    $editor = File::get(resource_path('views/forum/editor.blade.php'));

    expect($component)
        ->toContain('data-forum-error-summary')
        ->toContain('aria-live="assertive"')
        ->toContain('tabindex="-1"')
        ->toContain('data-error-field')
        ->and($adapter)
        ->toContain("document.addEventListener('livewire:navigated'")
        ->toContain("control.setAttribute('aria-invalid', 'true')")
        ->toContain("control.setAttribute('aria-describedby'")
        ->toContain("control.removeAttribute('aria-invalid')")
        ->toContain('MutationObserver')
        ->and($editor)
        ->toContain('data-forum-media-description')
        ->toContain('data-forum-video-transcript')
        ->toContain('data-forum-caption');
});

test('map components provide ordered textual alternatives', function () {
    foreach ([
        resource_path('views/components/place-map.blade.php'),
        resource_path('views/components/search-map.blade.php'),
    ] as $path) {
        $contents = File::get($path);

        expect($contents, $path)
            ->toContain('<ol')
            ->toContain('aria-label=');
    }
});

test('compliance matrix contains every canonical requirement exactly once', function () {
    $canonicalSources = [
        'docs/product-requirements.md',
        'docs/system-requirements.md',
        'docs/non-functional-requirements.md',
        'docs/requirements/laravel-engineering-standard.md',
    ];
    $canonicalIds = [];

    foreach ($canonicalSources as $source) {
        $contents = File::get(base_path($source));
        $tableMatches = [];
        $headingMatches = [];

        preg_match_all(
            '/^\|\s*([A-Z][A-Z0-9]*(?:-[A-Z0-9]+)*-\d{3})\s*\|/m',
            $contents,
            $tableMatches,
        );
        preg_match_all('/^###\s+(LAR-\d{2})\s+/m', $contents, $headingMatches);

        foreach ([...$tableMatches[1], ...$headingMatches[1]] as $id) {
            expect(array_key_exists($id, $canonicalIds))->toBeFalse($id);
            $canonicalIds[$id] = $source;
        }
    }

    $matrix = File::get(base_path('docs/requirements/compliance-matrix.md'));
    $matrixMatches = [];

    preg_match_all(
        '/^\|\s*((?:[A-Z][A-Z0-9]*(?:-[A-Z0-9]+)*-\d{3})|(?:LAR-\d{2}))\s*\|/m',
        $matrix,
        $matrixMatches,
    );

    expect($matrixMatches[1])
        ->toHaveCount(count($canonicalIds))
        ->and(array_values(array_unique($matrixMatches[1])))
        ->toHaveCount(count($canonicalIds))
        ->and(array_diff(array_keys($canonicalIds), $matrixMatches[1]))
        ->toBeEmpty()
        ->and(array_diff($matrixMatches[1], array_keys($canonicalIds)))
        ->toBeEmpty();
});

test('forum atomic requirements and evidence remain deterministic and traceable', function () {
    $preservation = Process::path(base_path())
        ->timeout(30)
        ->run([PHP_BINARY, 'scripts/preserve-forum-source-prompt.php', '--check']);
    $result = Process::path(base_path())
        ->timeout(30)
        ->run([PHP_BINARY, 'scripts/generate-forum-requirements.php', '--check']);
    $catalogue = json_decode(
        File::get(base_path('docs/requirements/forum-requirements.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $requirements = collect($catalogue['requirements']);

    expect(
        $preservation->successful(),
        $preservation->errorOutput().$preservation->output(),
    )->toBeTrue()
        ->and($result->successful(), $result->errorOutput().$result->output())
        ->toBeTrue()
        ->and($catalogue['source_payload_sha256'])
        ->toBe('9f52b2f90c8f1d0dc1c957f0207b6bd89c9a57eaf3359838e51b6c377e25458d')
        ->and($catalogue['source_parts'])
        ->toBe([
            'primary',
            'extension',
            'pet-profile-revision',
            'social-relationships-revision',
            'content-feed-revision',
            'communication-revision',
            'community-revision',
            'medical-record-revision',
        ])
        ->and($requirements)->toHaveCount(29960)
        ->and($requirements->pluck('requirement_id')->unique())->toHaveCount(29960)
        ->and($requirements->where('source_part', 'pet-profile-revision'))
        ->toHaveCount(4135)
        ->and($requirements->where('source_part', 'pet-profile-revision')
            ->pluck('requirement_id')
            ->filter(fn (string $id): bool => str_starts_with($id, 'pet.')))
        ->toHaveCount(4135)
        ->and($requirements->where('source_part', 'social-relationships-revision'))
        ->toHaveCount(3210)
        ->and($requirements->where('source_part', 'social-relationships-revision')
            ->pluck('requirement_id')
            ->filter(fn (string $id): bool => str_starts_with($id, 'social.')))
        ->toHaveCount(3210)
        ->and($requirements->where('source_part', 'content-feed-revision'))
        ->toHaveCount(4011)
        ->and($requirements->where('source_part', 'content-feed-revision')
            ->pluck('requirement_id')
            ->filter(fn (string $id): bool => str_starts_with($id, 'content.')))
        ->toHaveCount(4011)
        ->and($requirements->where('source_part', 'content-feed-revision')
            ->filter(fn (array $requirement): bool => $requirement['implementation_phase'] < 36
                || $requirement['implementation_phase'] > 44))
        ->toBeEmpty()
        ->and($requirements->where('source_part', 'communication-revision'))
        ->toHaveCount(3877)
        ->and($requirements->where('source_part', 'communication-revision')
            ->pluck('requirement_id')
            ->filter(fn (string $id): bool => str_starts_with($id, 'communication.')))
        ->toHaveCount(3877)
        ->and($requirements->where('source_part', 'communication-revision')
            ->filter(fn (array $requirement): bool => $requirement['implementation_phase'] < 46
                || $requirement['implementation_phase'] > 54))
        ->toBeEmpty()
        ->and($requirements->where('source_part', 'community-revision'))
        ->toHaveCount(3576)
        ->and($requirements->where('source_part', 'community-revision')
            ->pluck('requirement_id')
            ->filter(fn (string $id): bool => str_starts_with($id, 'community.')))
        ->toHaveCount(3576)
        ->and($requirements->where('source_part', 'community-revision')
            ->filter(fn (array $requirement): bool => $requirement['implementation_phase'] < 55
                || $requirement['implementation_phase'] > 63))
        ->toBeEmpty()
        ->and($requirements->where('source_part', 'medical-record-revision'))
        ->toHaveCount(3867)
        ->and($requirements->where('source_part', 'medical-record-revision')
            ->pluck('requirement_id')
            ->filter(fn (string $id): bool => str_starts_with($id, 'medical.')))
        ->toHaveCount(3867)
        ->and($requirements->where('source_part', 'medical-record-revision')
            ->filter(fn (array $requirement): bool => $requirement['implementation_phase'] < 64
                || $requirement['implementation_phase'] > 73))
        ->toBeEmpty();

    $requirements
        ->where('verification_status', 'verified')
        ->each(function (array $requirement): void {
            expect($requirement['evidence'], $requirement['requirement_id'])
                ->toBeArray()
                ->not->toBeEmpty()
                ->and($requirement['final_result'])
                ->toBe('verified');
        });
});

test('application source avoids prohibited database mass assignment and service locator calls', function () {
    $prohibitedDatabaseCalls = '/\bDB::(?:select|statement|raw|unprepared)\s*\(|->(?:selectRaw|whereRaw|orWhereRaw|havingRaw|orderByRaw|groupByRaw)\s*\(/';
    $unfilteredRequestPayloads = '/(?:\brequest\s*\(\s*\)|\$[A-Za-z_][A-Za-z0-9_]*request)\s*->\s*all\s*\(/i';
    $unsafeUploadPaths = '/->move\s*\(|->storeAs\s*\([^;]*getClientOriginalName/s';

    foreach (sourceFiles(app_path()) as $file) {
        $contents = $file->getContents();

        expect(
            preg_match($prohibitedDatabaseCalls, $contents),
            $file->getRelativePathname(),
        )->toBe(0)
            ->and(
                preg_match($unfilteredRequestPayloads, $contents),
                $file->getRelativePathname(),
            )->toBe(0)
            ->and(
                preg_match($unsafeUploadPaths, $contents),
                $file->getRelativePathname(),
            )->toBe(0);
    }

    foreach (sourceFiles(app_path('Http/Requests')) as $file) {
        expect($file->getContents(), $file->getRelativePathname())
            ->not->toMatch('/\bapp\s*\(/');
    }
});

test('first party source contains no volt components or debug calls', function () {
    $sourcePaths = [
        app_path(),
        base_path('bootstrap'),
        base_path('database'),
        resource_path('views'),
        base_path('routes'),
    ];
    $voltPatterns = [
        '/\bLivewire\\\\Volt\b/',
        '/\bVolt::/',
        '/\bnew\s+class\s+extends\s+Component\b/',
    ];
    $debugPattern = '/\b(?:dd|dump|ray|var_dump|print_r)\s*\(/';

    foreach ($sourcePaths as $path) {
        foreach (sourceFiles($path) as $file) {
            $contents = $file->getContents();

            foreach ($voltPatterns as $pattern) {
                expect(
                    preg_match($pattern, $contents),
                    $file->getRelativePathname(),
                )->toBe(0);
            }

            expect(
                preg_match($debugPattern, $contents),
                $file->getRelativePathname(),
            )->toBe(0);
        }
    }
});

test('tailwind utility names are statically discoverable', function () {
    $frontendPaths = [
        app_path(),
        resource_path(),
    ];
    $dynamicUtilityPattern = '/\b(?:bg|text|border|ring|outline|fill|stroke|grid-cols|col-span|from|via|to)-\$\{|'
        .'\b(?:bg|text|border|ring|outline|fill|stroke|grid-cols|col-span|from|via|to)-\{\{\s*\$/';

    foreach ($frontendPaths as $path) {
        foreach (File::allFiles($path) as $file) {
            if (! in_array($file->getExtension(), ['php', 'js', 'css'], true)) {
                continue;
            }

            expect(
                preg_match($dynamicUtilityPattern, $file->getContents()),
                $file->getRelativePathname(),
            )->toBe(0);
        }
    }
});

test('environment variables are only read by configuration files', function () {
    $runtimePaths = [
        app_path(),
        base_path('bootstrap'),
        base_path('database'),
        base_path('routes'),
    ];

    foreach ($runtimePaths as $path) {
        foreach (sourceFiles($path) as $file) {
            expect($file->getContents(), $file->getRelativePathname())
                ->not->toMatch('/\benv\s*\(/');
        }
    }
});

test('fresh database verifier enters testing environment before application bootstrap', function () {
    $source = File::get(base_path('scripts/verify-fresh-database.php'));
    $testingEnvironment = strpos($source, "putenv('APP_ENV=testing')");
    $applicationBootstrap = strpos($source, "require dirname(__DIR__).'/bootstrap/app.php'");

    expect($testingEnvironment)->not->toBeFalse()
        ->and($applicationBootstrap)->not->toBeFalse()
        ->and($testingEnvironment)->toBeLessThan($applicationBootstrap);
});

test('every application model has explicit fillable fields and a factory', function () {
    foreach (File::files(app_path('Models')) as $file) {
        $modelClass = 'App\\Models\\'.$file->getFilenameWithoutExtension();

        if (! is_subclass_of($modelClass, Model::class)) {
            continue;
        }

        $model = new $modelClass;
        $factoryClass = 'Database\\Factories\\'.$file->getFilenameWithoutExtension().'Factory';

        expect(in_array(HasFactory::class, class_uses_recursive($modelClass), true), $modelClass)
            ->toBeTrue()
            ->and(class_exists($factoryClass), $factoryClass)
            ->toBeTrue()
            ->and($model->getFillable(), $modelClass)
            ->not->toBeEmpty();
    }
});

test('project routes are named and controller backed', function () {
    $infrastructureUris = ['_boost/browser-logs', 'storage/{path}', 'up'];

    collect(RouteFacade::getRoutes())
        ->reject(
            fn (Route $route): bool => in_array($route->uri(), $infrastructureUris, true)
                || str_starts_with($route->uri(), 'livewire-'),
        )
        ->each(function (Route $route): void {
            expect($route->getName(), $route->uri())->not->toBeNull();
            expect($route->getActionName(), $route->uri())->not->toBe('Closure');
        });
});

test('every first party route has an explicit php test reference', function () {
    /** @var array<string, string> $coverageManifest */
    $coverageManifest = require base_path('tests/Support/route-coverage.php');

    foreach ($coverageManifest as $routeName => $testFile) {
        expect(RouteFacade::has($routeName), $routeName)->toBeTrue()
            ->and(File::exists(base_path($testFile)), $testFile)->toBeTrue();
    }

    $testSource = collect(sourceFiles(base_path('tests')))
        ->reject(fn (SplFileInfo $file): bool => $file->getRealPath() === __FILE__)
        ->map(fn (SplFileInfo $file): string => $file->getContents())
        ->implode("\n");

    collect(RouteFacade::getRoutes())
        ->filter(fn (Route $route): bool => str_starts_with($route->getActionName(), 'App\\'))
        ->each(function (Route $route) use ($testSource): void {
            $name = $route->getName();
            $actionClass = $route->getActionName();
            $actionName = class_basename($actionClass);

            expect($name, $route->uri())->not->toBeNull();
            expect(
                str_contains($testSource, "'{$name}'")
                    || str_contains($testSource, "\"{$name}\"")
                    || str_contains($testSource, $actionClass)
                    || str_contains($testSource, $actionName),
                $name,
            )->toBeTrue();
        });
});

test('responses include baseline browser security headers', function () {
    auth()->logout();

    $this->get(route('login'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=(self)');
});
