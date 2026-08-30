<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

test('the repository test runner ignores hostile inherited database settings', function () {
    $sentinelDatabase = tempnam(sys_get_temp_dir(), 'laravel-runner-sentinel-');

    if ($sentinelDatabase === false) {
        throw new RuntimeException('Unable to create the runner isolation sentinel database.');
    }

    $sentinel = new PDO('sqlite:'.$sentinelDatabase);
    $sentinel->exec('CREATE TABLE sentinel_records (id INTEGER PRIMARY KEY, value TEXT NOT NULL)');
    $sentinel->exec("INSERT INTO sentinel_records (value) VALUES ('must remain untouched')");
    $sentinel = null;
    $sentinelHash = hash_file('sha256', $sentinelDatabase);

    try {
        $process = new Process(
            [
                PHP_BINARY,
                base_path('scripts/run-tests.php'),
                '--compact',
                'tests/Feature/Runtime/RuntimeArtisanCommandTest.php',
                '--filter=the test process never loads the deployed configuration cache',
            ],
            base_path(),
            [
                'APP_CONFIG_CACHE' => $sentinelDatabase.'.config.php',
                'APP_ENV' => 'production',
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => $sentinelDatabase,
                'DB_URL' => 'sqlite:///'.$sentinelDatabase,
                'CACHE_STORE' => 'database',
                'MAIL_MAILER' => 'smtp',
                'QUEUE_CONNECTION' => 'database',
                'SESSION_DRIVER' => 'database',
            ],
        );
        $process->setTimeout(120);
        $process->start();
        $runnerProcessId = $process->getPid();
        $process->wait();

        $sentinel = new PDO('sqlite:'.$sentinelDatabase);
        $tables = $sentinel
            ->query("SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name")
            ?->fetchAll(PDO::FETCH_COLUMN);
        $value = $sentinel
            ->query('SELECT value FROM sentinel_records WHERE id = 1')
            ?->fetchColumn();

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput().$process->getOutput())
            ->and($process->getOutput())->toContain('"result":"passed","tests":1')
            ->and(hash_file('sha256', $sentinelDatabase))->toBe($sentinelHash)
            ->and($tables)->toBe(['sentinel_records'])
            ->and($value)->toBe('must remain untouched')
            ->and($runnerProcessId)->toBeInt()
            ->and(glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-tests-'.$runnerProcessId.'-*'))
            ->toBe([]);
    } finally {
        $sentinel = null;

        if (is_file($sentinelDatabase)) {
            unlink($sentinelDatabase);
        }
    }
});
