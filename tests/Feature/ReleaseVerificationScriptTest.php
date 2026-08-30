<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Process;

test('destructive database verification scripts fail closed on an uncaught exception', function (string $script): void {
    $missingTemporaryDirectory = sys_get_temp_dir().'/pawcircle-missing-verification-runtime-'.bin2hex(random_bytes(8));

    $result = Process::path(base_path())->run([
        PHP_BINARY,
        '-d',
        'sys_temp_dir='.$missingTemporaryDirectory,
        $script,
    ]);

    expect(is_dir($missingTemporaryDirectory))->toBeFalse()
        ->and($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('verification failed:')
        ->and($result->output())->toBe('');
})->with([
    'fresh migration and seed' => 'scripts/verify-fresh-database.php',
    'migration rollback and reapply' => 'scripts/verify-migration-cycle.php',
]);
