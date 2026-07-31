<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

require dirname(__DIR__).'/vendor/autoload.php';

$application = require dirname(__DIR__).'/bootstrap/app.php';
$application->make(Kernel::class)->bootstrap();

$temporaryDirectory = realpath(sys_get_temp_dir());

if ($temporaryDirectory === false) {
    throw new RuntimeException('The system temporary directory is unavailable.');
}

$database = $temporaryDirectory.'/mengto-fresh-verification-'.bin2hex(random_bytes(8)).'.sqlite';

if (dirname($database) !== $temporaryDirectory) {
    throw new RuntimeException('Refusing destructive verification outside the system temporary directory.');
}

if (! touch($database)) {
    throw new RuntimeException('Unable to create the temporary verification database.');
}

config()->set('database.default', 'sqlite');
config()->set('database.connections.sqlite.url', null);
config()->set('database.connections.sqlite.database', $database);
DB::purge('sqlite');

if (config('database.connections.sqlite.database') !== $database) {
    throw new RuntimeException('Temporary database configuration assertion failed.');
}

try {
    $freshExit = Artisan::call('migrate:fresh', [
        '--database' => 'sqlite',
        '--seed' => true,
        '--force' => true,
        '--no-interaction' => true,
    ]);

    if ($freshExit !== 0) {
        throw new RuntimeException('Fresh migration and seeding failed: '.Artisan::output());
    }

    $tables = count(Schema::getTables());
    $migrations = DB::table('migrations')->count();
    $usersBeforeRepeat = DB::table('users')->count();

    $repeatExit = Artisan::call('db:seed', [
        '--database' => 'sqlite',
        '--force' => true,
        '--no-interaction' => true,
    ]);
    $usersAfterRepeat = DB::table('users')->count();

    if ($repeatExit !== 0) {
        throw new RuntimeException('Repeated seeding failed: '.Artisan::output());
    }

    if ($usersBeforeRepeat !== $usersAfterRepeat) {
        throw new RuntimeException('Repeated seeding changed the stable user count.');
    }

    echo json_encode([
        'database' => $database,
        'tables' => $tables,
        'migrations' => $migrations,
        'users_before_repeat' => $usersBeforeRepeat,
        'users_after_repeat' => $usersAfterRepeat,
        'fresh_exit' => $freshExit,
        'repeat_seed_exit' => $repeatExit,
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT).PHP_EOL;
} finally {
    DB::disconnect('sqlite');

    if (is_file($database) && ! unlink($database)) {
        fwrite(STDERR, "Unable to remove temporary verification database: {$database}\n");
    }
}
