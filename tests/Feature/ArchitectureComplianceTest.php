<?php

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;
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
            ->not->toContain('{!!');

        expect(
            preg_match('/\b[A-Z][A-Za-z0-9_\\\\]*::[A-Za-z_][A-Za-z0-9_]*\s*\(/', $contents),
            $file->getRelativePathname(),
        )->toBe(0);
    }
});

test('application source avoids prohibited database and service locator calls', function () {
    $prohibitedDatabaseCalls = '/\bDB::(?:select|statement|raw|unprepared)\s*\(|->(?:selectRaw|whereRaw|orWhereRaw|havingRaw|orderByRaw|groupByRaw)\s*\(/';

    foreach (sourceFiles(app_path()) as $file) {
        $contents = $file->getContents();

        expect(
            preg_match($prohibitedDatabaseCalls, $contents),
            $file->getRelativePathname(),
        )->toBe(0);
    }

    foreach (sourceFiles(app_path('Http/Requests')) as $file) {
        expect($file->getContents(), $file->getRelativePathname())
            ->not->toMatch('/\bapp\s*\(/');
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
        ->reject(fn (Route $route): bool => in_array($route->uri(), $infrastructureUris, true))
        ->each(function (Route $route): void {
            expect($route->getName(), $route->uri())->not->toBeNull();
            expect($route->getActionName(), $route->uri())->not->toBe('Closure');
        });
});

test('responses include baseline browser security headers', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=(self)');
});
