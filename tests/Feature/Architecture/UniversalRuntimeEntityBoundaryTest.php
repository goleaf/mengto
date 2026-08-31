<?php

declare(strict_types=1);

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;

/** @return list<string> */
function universalRuntimeSourceFiles(): array
{
    return collect([
        app_path(),
        base_path('routes'),
        resource_path('views'),
        resource_path('js'),
        resource_path('css'),
        resource_path('scss'),
        lang_path(),
        config_path(),
        base_path('bootstrap'),
    ])
        ->filter(static fn (string $path): bool => File::isDirectory($path))
        ->flatMap(static fn (string $root) => File::allFiles($root))
        ->filter(static fn (SplFileInfo $file): bool => in_array(
            $file->getExtension(),
            ['php', 'js', 'mjs', 'css', 'scss'],
            true,
        ))
        ->map(static fn (SplFileInfo $file): string => $file->getPathname())
        ->values()
        ->all();
}

test('production runtime contains no built in person or pet instances', function (): void {
    $forbidden = '/\b(?:Mia Carter|Ari Jensen|Jamie Cho|Lena Brooks|Priya Shah|Theo|Scout|Nori|Mochi|Bean)\b|mia-carter|ari-jensen|owner-(?:mia-carter|ari-jensen)/iu';
    $matches = [];

    foreach (universalRuntimeSourceFiles() as $path) {
        if (preg_match($forbidden, File::get($path), $match) !== 1) {
            continue;
        }

        $matches[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path).': '.$match[0];
    }

    expect($matches)->toBe([]);
});

test('production routes contain no literal entity instances defaults or allowlists', function (): void {
    $violations = collect(app('router')->getRoutes()->getRoutes())
        ->filter(static function (Route $route): bool {
            $uri = $route->uri();
            $defaults = $route->getAction('defaults');
            $wheres = $route->wheres;

            $forbidden = '/mia-carter|ari-jensen|(?:^|[\/"\x27])(scout|nori|mochi|bean|apartment-pets|trail-tails|cat-people|foster-network|portland-labradors|senior-companions)(?:$|[\/"\x27])/iu';

            return preg_match($forbidden, $uri) === 1
                || preg_match($forbidden, json_encode([$defaults, $wheres], JSON_THROW_ON_ERROR)) === 1;
        })
        ->map(static fn (Route $route): string => ($route->getName() ?? '(unnamed)').': '.$route->uri())
        ->values()
        ->all();

    expect($violations)->toBe([]);
});

test('normal production routes do not depend on prototype entity stores', function (): void {
    $routeControllers = collect(app('router')->getRoutes()->getRoutes())
        ->map(static fn (Route $route): string => $route->getActionName())
        ->filter(static fn (string $action): bool => str_starts_with($action, 'App\\'))
        ->unique()
        ->values();
    $violations = [];

    foreach ($routeControllers as $controller) {
        $path = (new ReflectionClass($controller))->getFileName();

        if (! is_string($path)) {
            continue;
        }

        $source = File::get($path);

        if (preg_match('/\b(?:PrototypeState|PreviewService)\b/u', $source) === 1) {
            $violations[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
        }
    }

    expect($violations)->toBe([]);
});

test('runtime code never invokes seeders or factories', function (): void {
    $violations = [];

    foreach (universalRuntimeSourceFiles() as $path) {
        $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);

        if (str_starts_with($relative, 'app/Models/')) {
            continue;
        }

        $source = File::get($path);

        if (preg_match('/Database\\\\(?:Seeders|Factories)\\\\|::factory\s*\(/u', $source) === 1) {
            $violations[] = $relative;
        }
    }

    expect($violations)->toBe([]);
});
