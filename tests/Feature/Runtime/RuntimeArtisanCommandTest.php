<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

test('runtime artisan wrapper executes Laravel as the service account', function () {
    $script = base_path('scripts/artisan-runtime');

    expect(is_file($script))->toBeTrue()
        ->and(is_executable($script))->toBeTrue();

    $process = new Process(
        [$script, '--version'],
        base_path(),
        [
            'APP_CONFIG_CACHE' => false,
            'APP_EVENTS_CACHE' => false,
            'APP_PACKAGES_CACHE' => false,
            'APP_ROUTES_CACHE' => false,
            'APP_SERVICES_CACHE' => false,
            'LARAVEL_STORAGE_PATH' => base_path('storage'),
        ],
    );
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
        ->and(trim($process->getOutput()))->toStartWith('Laravel Framework 13.');
});

test('the test process never loads the deployed configuration cache', function () {
    expect(app()->environment())->toBe('testing')
        ->and(config('database.default'))->toBe('sqlite')
        ->and(config('database.connections.sqlite.url'))->toBe('')
        ->and(config('database.connections.sqlite.database'))->toBe(':memory:')
        ->and(config('cache.default'))->toBe('array')
        ->and(config('mail.default'))->toBe('array')
        ->and(config('queue.default'))->toBe('sync')
        ->and(config('session.driver'))->toBe('array')
        ->and(DB::connection()->getDriverName())->toBe('sqlite')
        ->and(DB::connection()->getDatabaseName())->toBe(':memory:')
        ->and(storage_path())->toStartWith(sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-tests-');

    expect(storage_path() !== base_path('storage'))->toBeTrue();
});
