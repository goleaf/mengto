<?php

declare(strict_types=1);

use Illuminate\Contracts\Config\Repository as ConfigurationRepository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);
$checks = [
    'a11y' => ['scripts/accessibility-browser-check.mjs'],
    'groups' => ['scripts/accessibility-browser-check.mjs', '--groups-only'],
    'animal-science' => ['scripts/accessibility-browser-check.mjs', '--animal-science-only'],
    'page-identity' => ['scripts/accessibility-browser-check.mjs', '--page-identity-only'],
    'places' => ['scripts/accessibility-browser-check.mjs', '--places-only'],
    'discover' => ['scripts/discovery-browser-check.mjs'],
    'onboarding' => ['scripts/accessibility-browser-check.mjs', '--onboarding-only'],
];
$name = $argv[1] ?? '';
$isolationOnly = ($argv[2] ?? '') === '--assert-isolation';

if (! isset($checks[$name])) {
    fwrite(STDERR, 'Usage: php scripts/run-browser-check.php '.implode('|', array_keys($checks)).PHP_EOL);

    exit(2);
}

$database = tempnam(sys_get_temp_dir(), 'laravel-browser-db-');
$outputDirectory = sys_get_temp_dir().'/laravel-browser-output-'.bin2hex(random_bytes(8));
$configCache = sys_get_temp_dir().'/laravel-browser-config-'.bin2hex(random_bytes(8)).'.php';
$storageDirectory = $outputDirectory.'/storage';
$cacheDirectory = $storageDirectory.'/framework/cache';
$filesystem = new Filesystem;

if (! is_string($database) || ! str_starts_with($database, sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-browser-db-')) {
    throw new RuntimeException('Unable to create a verified temporary browser database.');
}

if (! mkdir($outputDirectory, 0700) && ! is_dir($outputDirectory)) {
    throw new RuntimeException('Unable to create the temporary browser output directory.');
}

$socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);

if ($socket === false) {
    throw new RuntimeException("Unable to reserve a loopback port: {$errorCode} {$errorMessage}");
}

$socketName = stream_socket_get_name($socket, false);
fclose($socket);

if (! is_string($socketName) || preg_match('/:(\d+)$/', $socketName, $portMatch) !== 1) {
    throw new RuntimeException('Unable to determine the reserved loopback port.');
}

$baseUrl = 'http://127.0.0.1:'.$portMatch[1];
$environment = [
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'false',
    'APP_URL' => $baseUrl,
    'APP_CONFIG_CACHE' => $configCache,
    'APP_EVENTS_CACHE' => $cacheDirectory.'/events.php',
    'APP_PACKAGES_CACHE' => $cacheDirectory.'/packages.php',
    'APP_ROUTES_CACHE' => $cacheDirectory.'/routes-v7.php',
    'APP_SERVICES_CACHE' => $cacheDirectory.'/services.php',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $database,
    'DB_URL' => false,
    'EMAIL_VERIFICATION_ENABLED' => 'true',
    'CACHE_STORE' => 'array',
    'LARAVEL_STORAGE_PATH' => $storageDirectory,
    'MAIL_MAILER' => 'array',
    'SESSION_DRIVER' => 'database',
    'QUEUE_CONNECTION' => 'sync',
    'VIEW_COMPILED_PATH' => $storageDirectory.'/framework/views',
];
$server = null;
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
        $filesystem->ensureDirectoryExists($storageDirectory.'/'.$directory, 0700);
    }

    foreach ($environment as $key => $value) {
        if ($value === false) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);

            continue;
        }

        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    $application = require $root.'/bootstrap/app.php';
    $application->make(Kernel::class)->bootstrap();
    $configuration = $application->make(ConfigurationRepository::class);
    $databaseManager = $application->make(DatabaseManager::class);
    $connection = $databaseManager->connection('sqlite');
    $resolvedDatabase = $connection->getDatabaseName();
    $resolvedDriver = $connection->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);

    if (! $application->environment('testing')
        || $configuration->get('database.default') !== 'sqlite'
        || $configuration->get('database.connections.sqlite.url') !== null
        || $configuration->get('database.connections.sqlite.database') !== $database
        || $resolvedDriver !== 'sqlite'
        || $resolvedDatabase !== $database
        || $application->storagePath() !== $storageDirectory) {
        throw new RuntimeException('The browser runner refused an unsafe resolved runtime.');
    }

    $databaseManager->disconnect('sqlite');

    if ($isolationOnly) {
        fwrite(STDOUT, json_encode([
            'app_env' => $environment['APP_ENV'],
            'database_connection' => $environment['DB_CONNECTION'],
            'database_path' => $database,
            'resolved_database_path' => $resolvedDatabase,
            'pdo_driver' => $resolvedDriver,
            'database_url' => $configuration->get('database.connections.sqlite.url'),
            'config_cache_path' => $configCache,
            'storage_path' => $storageDirectory,
            'loopback_url' => $baseUrl,
        ], JSON_THROW_ON_ERROR).PHP_EOL);
        $exitCode = 0;
    } else {
        $migrate = new Process(
            [PHP_BINARY, 'artisan', 'migrate:fresh', '--seed', '--force', '--no-interaction'],
            $root,
            $environment,
        );
        $migrate->setTimeout(180);
        $migrate->run(static function (string $type, string $buffer): void {
            fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
        });

        if (! $migrate->isSuccessful()) {
            throw new RuntimeException('The isolated browser database could not be prepared.');
        }

        if ($name === 'onboarding') {
            $seedOnboarding = new Process(
                [PHP_BINARY, 'artisan', 'db:seed', '--class=Database\\Seeders\\OnboardingBrowserSeeder', '--force', '--no-interaction'],
                $root,
                $environment,
            );
            $seedOnboarding->setTimeout(60);
            $seedOnboarding->run(static function (string $type, string $buffer): void {
                fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
            });

            if (! $seedOnboarding->isSuccessful()) {
                throw new RuntimeException('The isolated onboarding browser fixtures could not be prepared.');
            }
        }

        $server = new Process(
            [
                PHP_BINARY,
                '-S',
                '127.0.0.1:'.$portMatch[1],
                $root.'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php',
            ],
            $root.'/public',
            $environment,
        );
        $server->setTimeout(null);
        $server->disableOutput();
        $server->start();

        $ready = false;
        $deadline = microtime(true) + 20;

        while (microtime(true) < $deadline) {
            if (! $server->isRunning()) {
                throw new RuntimeException(sprintf(
                    'The isolated browser server stopped during startup with exit code %s.',
                    (string) ($server->getExitCode() ?? 'unknown'),
                ));
            }

            $context = stream_context_create(['http' => [
                'ignore_errors' => true,
                'timeout' => 1,
            ]]);
            $response = @file_get_contents($baseUrl.'/login', false, $context);

            if (is_string($response)) {
                $ready = true;

                break;
            }

            usleep(100_000);
        }

        if (! $ready) {
            throw new RuntimeException('The isolated browser server did not become ready.');
        }

        $node = new Process(
            ['node', ...$checks[$name]],
            $root,
            [
                ...$environment,
                'BROWSER_BASE_URL' => $baseUrl,
                'BROWSER_ALLOW_DATA_MUTATION' => '1',
                'BROWSER_OUTPUT_DIR' => $outputDirectory,
            ],
        );
        $node->setTimeout(900);
        $node->run(static function (string $type, string $buffer): void {
            fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
        });
        $exitCode = $node->getExitCode() ?? 1;
    }
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
} finally {
    if ($server instanceof Process && $server->isRunning()) {
        $server->stop(3);
    }

    if (is_file($database)) {
        unlink($database);
    }

    if (is_file($configCache)) {
        unlink($configCache);
    }

    if (is_dir($outputDirectory)
        && str_starts_with($outputDirectory, sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-browser-output-')) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($outputDirectory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $entry) {
            if (! $entry instanceof SplFileInfo) {
                continue;
            }

            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }

        rmdir($outputDirectory);
    }
}

exit($exitCode);
