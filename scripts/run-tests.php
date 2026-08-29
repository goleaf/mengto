<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);
$clear = new Process([PHP_BINARY, 'artisan', 'config:clear', '--ansi'], $root);
$clear->setTimeout(30);
$clear->run(static function (string $type, string $buffer): void {
    fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
});

if (! $clear->isSuccessful()) {
    exit($clear->getExitCode() ?? 1);
}

$testStorage = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pawcircle-tests-'.getmypid().'-'.bin2hex(random_bytes(6));
$filesystem = new Filesystem;

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

$test = new Process(
    [PHP_BINARY, 'artisan', 'test', ...array_slice($argv, 1)],
    $root,
    ['LARAVEL_STORAGE_PATH' => $testStorage],
);
$test->setTimeout(null);

try {
    $test->run(static function (string $type, string $buffer): void {
        fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
    });
} finally {
    $filesystem->deleteDirectory($testStorage);
}

exit($test->getExitCode() ?? 1);
