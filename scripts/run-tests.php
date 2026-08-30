<?php

declare(strict_types=1);

use Illuminate\Contracts\Config\Repository as ConfigurationRepository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);
$argv = array_values(array_filter(
    $_SERVER['argv'] ?? [],
    static fn (mixed $argument): bool => is_string($argument),
));
$temporaryRoot = realpath(sys_get_temp_dir());

if ($temporaryRoot === false) {
    throw new RuntimeException('The system temporary directory is unavailable.');
}

$testStorage = $temporaryRoot.DIRECTORY_SEPARATOR.'laravel-tests-'.getmypid().'-'.bin2hex(random_bytes(6));
$filesystem = new Filesystem;

$filesystem->ensureDirectoryExists($testStorage, 0770);
$resolvedTestStorage = realpath($testStorage);

if (
    $resolvedTestStorage === false
    || $resolvedTestStorage !== $testStorage
    || dirname($resolvedTestStorage) !== $temporaryRoot
    || ! preg_match('/^laravel-tests-[0-9]+-[a-f0-9]{12}$/', basename($resolvedTestStorage))
) {
    throw new RuntimeException('The test runner could not validate its temporary storage directory.');
}

$validatedTestStorage = $resolvedTestStorage;
$cacheDirectory = $testStorage.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'cache';
$environment = [
    'APP_CONFIG_CACHE' => $cacheDirectory.DIRECTORY_SEPARATOR.'config.php',
    'APP_ENV' => 'testing',
    'APP_EVENTS_CACHE' => $cacheDirectory.DIRECTORY_SEPARATOR.'events.php',
    'APP_PACKAGES_CACHE' => $cacheDirectory.DIRECTORY_SEPARATOR.'packages.php',
    'APP_ROUTES_CACHE' => $cacheDirectory.DIRECTORY_SEPARATOR.'routes-v7.php',
    'APP_SERVICES_CACHE' => $cacheDirectory.DIRECTORY_SEPARATOR.'services.php',
    'CACHE_STORE' => 'array',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => false,
    'EMAIL_VERIFICATION_ENABLED' => 'true',
    'LARAVEL_STORAGE_PATH' => $testStorage,
    'MAIL_MAILER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'SESSION_DRIVER' => 'array',
];
$exitCode = 1;

try {
    foreach ([
        'app/private',
        'app/public',
        'framework/cache/data',
        'framework/sessions',
        'framework/testing',
        'framework/views',
        'logs',
    ] as $directory) {
        $filesystem->ensureDirectoryExists($testStorage.DIRECTORY_SEPARATOR.$directory, 0770);
    }

    foreach ($environment as $name => $value) {
        if ($value === false) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);

            continue;
        }

        putenv($name.'='.$value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    $clear = new Process(
        [PHP_BINARY, 'artisan', 'config:clear', '--ansi'],
        $root,
        $environment,
    );
    $clear->setTimeout(30);
    $clear->run(static function (string $type, string $buffer): void {
        fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
    });

    if (! $clear->isSuccessful()) {
        $exitCode = $clear->getExitCode() ?? 1;
    } else {
        $application = require $root.'/bootstrap/app.php';
        $application->make(Kernel::class)->bootstrap();

        $configuration = $application->make(ConfigurationRepository::class);
        $database = $application->make(DatabaseManager::class);
        $connection = $database->connection('sqlite');

        if (
            ! $application->environment('testing')
            || $configuration->get('database.default') !== 'sqlite'
            || $configuration->get('database.connections.sqlite.url') !== null
            || $configuration->get('database.connections.sqlite.database') !== ':memory:'
            || $connection->getDriverName() !== 'sqlite'
            || $connection->getDatabaseName() !== ':memory:'
            || $connection->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite'
        ) {
            throw new RuntimeException('The test runner refused an unsafe resolved database connection.');
        }

        $database->disconnect('sqlite');

        $test = new Process(
            [PHP_BINARY, 'artisan', 'test', ...array_slice($argv, 1)],
            $root,
            $environment,
        );
        $test->setTimeout(null);

        $test->run(static function (string $type, string $buffer): void {
            fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
        });

        $exitCode = $test->getExitCode() ?? 1;
    }
} finally {
    if (
        dirname($validatedTestStorage) === $temporaryRoot
        && preg_match('/^laravel-tests-[0-9]+-[a-f0-9]{12}$/', basename($validatedTestStorage))
    ) {
        $filesystem->deleteDirectory($validatedTestStorage);
    }
}

exit($exitCode);
