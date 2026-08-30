<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';
putenv('EMAIL_VERIFICATION_ENABLED=true');
$_ENV['EMAIL_VERIFICATION_ENABLED'] = 'true';
$_SERVER['EMAIL_VERIFICATION_ENABLED'] = 'true';

$reportUncaughtFailure = static function (Throwable $throwable): never {
    fwrite(STDERR, 'Migration cycle verification failed: '.$throwable->getMessage().PHP_EOL);
    exit(1);
};
set_exception_handler($reportUncaughtFailure);

require dirname(__DIR__).'/vendor/autoload.php';

$application = require dirname(__DIR__).'/bootstrap/app.php';
$application->make(Kernel::class)->bootstrap();
set_exception_handler($reportUncaughtFailure);

$temporaryDirectory = realpath(sys_get_temp_dir());

if ($temporaryDirectory === false) {
    throw new RuntimeException('The system temporary directory is unavailable.');
}

$database = $temporaryDirectory.'/mengto-migration-cycle-'.bin2hex(random_bytes(8)).'.sqlite';

if (dirname($database) !== $temporaryDirectory) {
    throw new RuntimeException('Refusing migration-cycle verification outside the system temporary directory.');
}

if (! touch($database)) {
    throw new RuntimeException('Unable to create the temporary migration-cycle database.');
}

try {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.url', null);
    config()->set('database.connections.sqlite.database', $database);
    DB::purge('sqlite');

    if (config('database.connections.sqlite.database') !== $database) {
        throw new RuntimeException('Temporary database configuration assertion failed.');
    }

    $migrationFiles = glob(dirname(__DIR__).'/database/migrations/*.php');

    if ($migrationFiles === false || $migrationFiles === []) {
        throw new RuntimeException('No migration files were discovered.');
    }

    $expectedLedger = array_map(
        static fn (string $path): string => pathinfo($path, PATHINFO_FILENAME),
        $migrationFiles,
    );
    sort($expectedLedger);

    /** @return list<string> */
    $ledger = static function (): array {
        if (! Schema::hasTable('migrations')) {
            return [];
        }

        return DB::table('migrations')
            ->orderBy('migration')
            ->pluck('migration')
            ->map(static fn (mixed $migration): string => (string) $migration)
            ->all();
    };

    /** @param array<string, int|string|bool> $arguments */
    $run = static function (string $command, array $arguments): int {
        try {
            $exit = Artisan::call($command, $arguments);
        } catch (Throwable $throwable) {
            throw new RuntimeException(
                "{$command} raised an exception: ".trim(Artisan::output()).' '.$throwable->getMessage(),
                previous: $throwable,
            );
        }

        if ($exit !== 0) {
            throw new RuntimeException(
                "{$command} failed with exit code {$exit}: ".Artisan::output(),
            );
        }

        return $exit;
    };

    $firstApplyExit = $run('migrate', [
        '--database' => 'sqlite',
        '--force' => true,
        '--no-interaction' => true,
    ]);
    $firstLedger = $ledger();

    if ($firstLedger !== $expectedLedger) {
        throw new RuntimeException('The first migration ledger does not match every migration file.');
    }

    $rollbackExit = $run('migrate:rollback', [
        '--database' => 'sqlite',
        '--step' => count($firstLedger),
        '--force' => true,
        '--no-interaction' => true,
    ]);
    $remainingAfterRollback = count($ledger());

    if ($remainingAfterRollback !== 0 || Schema::hasTable('users')) {
        throw new RuntimeException('The complete rollback left application migrations applied.');
    }

    $secondApplyExit = $run('migrate', [
        '--database' => 'sqlite',
        '--force' => true,
        '--no-interaction' => true,
    ]);
    $secondLedger = $ledger();

    if ($secondLedger !== $expectedLedger) {
        throw new RuntimeException('The second migration ledger does not match every migration file.');
    }

    $seedExit = $run('db:seed', [
        '--database' => 'sqlite',
        '--force' => true,
        '--no-interaction' => true,
    ]);
    $usersBeforeRepeat = DB::table('users')->count();
    $repeatSeedExit = $run('db:seed', [
        '--database' => 'sqlite',
        '--force' => true,
        '--no-interaction' => true,
    ]);
    $usersAfterRepeat = DB::table('users')->count();

    if ($usersBeforeRepeat !== $usersAfterRepeat) {
        throw new RuntimeException('Repeated seeding changed the stable user count.');
    }

    echo json_encode([
        'database' => $database,
        'migration_files' => count($expectedLedger),
        'first_apply_migrations' => count($firstLedger),
        'remaining_after_rollback' => $remainingAfterRollback,
        'second_apply_migrations' => count($secondLedger),
        'first_ledger_matches_files' => true,
        'second_ledger_matches_files' => true,
        'tables_after_reapply' => count(Schema::getTables()),
        'users_before_repeat' => $usersBeforeRepeat,
        'users_after_repeat' => $usersAfterRepeat,
        'first_apply_exit' => $firstApplyExit,
        'rollback_exit' => $rollbackExit,
        'second_apply_exit' => $secondApplyExit,
        'seed_exit' => $seedExit,
        'repeat_seed_exit' => $repeatSeedExit,
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT).PHP_EOL;
} finally {
    DB::disconnect('sqlite');

    if (is_file($database) && ! unlink($database)) {
        fwrite(STDERR, "Unable to remove temporary migration-cycle database: {$database}\n");
    }
}
