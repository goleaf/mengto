<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';
putenv('DB_CONNECTION=sqlite');
$_ENV['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_CONNECTION'] = 'sqlite';
putenv('DB_URL');
unset($_ENV['DB_URL'], $_SERVER['DB_URL']);

$temporaryDirectory = realpath(sys_get_temp_dir());

if ($temporaryDirectory === false) {
    throw new RuntimeException('The system temporary directory is unavailable.');
}

$database = $temporaryDirectory.'/mengto-onboarding-migration-'.bin2hex(random_bytes(8)).'.sqlite';

if (dirname($database) !== $temporaryDirectory || ! touch($database)) {
    throw new RuntimeException('Unable to create a safe temporary onboarding migration database.');
}

putenv('DB_DATABASE='.$database);
$_ENV['DB_DATABASE'] = $database;
$_SERVER['DB_DATABASE'] = $database;

$reportUncaughtFailure = static function (Throwable $throwable) use ($database): never {
    if (is_file($database)) {
        unlink($database);
    }

    fwrite(STDERR, 'Onboarding migration verification failed: '.$throwable->getMessage().PHP_EOL);
    exit(1);
};
set_exception_handler($reportUncaughtFailure);

require dirname(__DIR__).'/vendor/autoload.php';

$application = require dirname(__DIR__).'/bootstrap/app.php';
$application->make(Kernel::class)->bootstrap();
set_exception_handler($reportUncaughtFailure);

$migrationName = '2026_08_30_270000_create_user_onboardings_table';
$targetMigration = dirname(__DIR__).'/database/migrations/'.$migrationName.'.php';

try {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.url', null);
    config()->set('database.connections.sqlite.database', $database);
    DB::purge('sqlite');

    if (config('database.connections.sqlite.database') !== $database) {
        throw new RuntimeException('Temporary database configuration assertion failed.');
    }

    $migrationFiles = glob(dirname(__DIR__).'/database/migrations/*.php');

    if ($migrationFiles === false || ! in_array($targetMigration, $migrationFiles, true)) {
        throw new RuntimeException('The canonical onboarding migration was not found.');
    }

    sort($migrationFiles);
    $priorMigrations = array_values(array_filter(
        $migrationFiles,
        static fn (string $path): bool => strcmp($path, $targetMigration) < 0,
    ));

    /** @param array<string, bool|int|string|list<string>> $arguments */
    $run = static function (string $command, array $arguments): int {
        try {
            $exit = Artisan::call($command, $arguments);
        } catch (Throwable $throwable) {
            throw new RuntimeException(
                $command.' raised an exception: '.trim(Artisan::output()).' '.$throwable->getMessage(),
                previous: $throwable,
            );
        }

        if ($exit !== 0) {
            throw new RuntimeException(
                $command.' failed with exit code '.$exit.': '.Artisan::output(),
            );
        }

        return $exit;
    };

    $run('migrate', [
        '--database' => 'sqlite',
        '--path' => $priorMigrations,
        '--realpath' => true,
        '--force' => true,
        '--no-interaction' => true,
    ]);

    if (Schema::hasTable('user_onboardings')) {
        throw new RuntimeException('Onboarding migration was applied before the legacy fixture was created.');
    }

    $createdAt = '2026-08-30 10:00:00';
    $legacyUsers = [
        ['active', true, false],
        ['unverified', false, false],
        ['blocked', true, false],
        ['suspended', true, false],
        ['administrator', true, true],
    ];

    foreach ($legacyUsers as [$status, $verified, $administrator]) {
        DB::table('users')->insert([
            'actor_key' => 'legacy-onboarding-'.$status,
            'name' => 'Legacy '.ucfirst($status),
            'email' => 'legacy-'.$status.'@example.test',
            'email_verified_at' => $verified ? $createdAt : null,
            'password' => 'legacy-test-password-hash',
            'locale' => 'lt',
            'timezone' => 'Europe/Vilnius',
            'status' => $status === 'administrator' || $status === 'unverified' ? 'active' : $status,
            'is_admin' => $administrator,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    $legacySnapshot = DB::table('users')
        ->select([
            'id',
            'actor_key',
            'email',
            'email_verified_at',
            'password',
            'locale',
            'timezone',
            'status',
            'is_admin',
        ])
        ->orderBy('id')
        ->get()
        ->map(static fn (object $user): array => (array) $user)
        ->all();

    $firstApplyExit = $run('migrate', [
        '--database' => 'sqlite',
        '--path' => [$targetMigration],
        '--realpath' => true,
        '--force' => true,
        '--no-interaction' => true,
    ]);
    $afterApply = DB::table('users')
        ->select(array_keys($legacySnapshot[0]))
        ->orderBy('id')
        ->get()
        ->map(static fn (object $user): array => (array) $user)
        ->all();
    $legacyUserPreservedAfterApply = $afterApply === $legacySnapshot;
    $legacyUserHasNoOnboardingRow = DB::table('user_onboardings')->count() === 0;

    $rollbackExit = $run('migrate:rollback', [
        '--database' => 'sqlite',
        '--path' => [$targetMigration],
        '--realpath' => true,
        '--step' => 1,
        '--force' => true,
        '--no-interaction' => true,
    ]);
    $onboardingTableRemovedAfterRollback = ! Schema::hasTable('user_onboardings');
    $legacyUserPreservedAfterRollback = DB::table('users')
        ->select(array_keys($legacySnapshot[0]))
        ->orderBy('id')
        ->get()
        ->map(static fn (object $user): array => (array) $user)
        ->all() === $legacySnapshot;

    $secondApplyExit = $run('migrate', [
        '--database' => 'sqlite',
        '--path' => [$targetMigration],
        '--realpath' => true,
        '--force' => true,
        '--no-interaction' => true,
    ]);
    $legacyUserPreservedAfterReapply = DB::table('users')
        ->select(array_keys($legacySnapshot[0]))
        ->orderBy('id')
        ->get()
        ->map(static fn (object $user): array => (array) $user)
        ->all() === $legacySnapshot;
    $legacyUserHasNoOnboardingRowAfterReapply = DB::table('user_onboardings')->count() === 0;

    foreach ([
        $legacyUserPreservedAfterApply,
        $legacyUserHasNoOnboardingRow,
        $onboardingTableRemovedAfterRollback,
        $legacyUserPreservedAfterRollback,
        $legacyUserPreservedAfterReapply,
        $legacyUserHasNoOnboardingRowAfterReapply,
    ] as $verified) {
        if (! $verified) {
            throw new RuntimeException('A legacy compatibility assertion failed.');
        }
    }

    echo json_encode([
        'migration' => $migrationName,
        'legacy_users' => count($legacySnapshot),
        'legacy_user_preserved_after_apply' => $legacyUserPreservedAfterApply,
        'legacy_user_has_no_onboarding_row' => $legacyUserHasNoOnboardingRow,
        'onboarding_table_removed_after_rollback' => $onboardingTableRemovedAfterRollback,
        'legacy_user_preserved_after_rollback' => $legacyUserPreservedAfterRollback,
        'legacy_user_preserved_after_reapply' => $legacyUserPreservedAfterReapply,
        'legacy_user_has_no_onboarding_row_after_reapply' => $legacyUserHasNoOnboardingRowAfterReapply,
        'first_apply_exit' => $firstApplyExit,
        'rollback_exit' => $rollbackExit,
        'second_apply_exit' => $secondApplyExit,
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT).PHP_EOL;
} finally {
    DB::disconnect('sqlite');

    if (is_file($database) && ! unlink($database)) {
        fwrite(STDERR, 'Unable to remove the temporary onboarding migration database.'.PHP_EOL);
    }
}
