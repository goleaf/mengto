<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

test('simultaneous joins allocate the final place once and deterministically waitlist the other participant', function (): void {
    $temporaryRoot = realpath(sys_get_temp_dir());
    expect($temporaryRoot)->not->toBeFalse();
    $workingDirectory = $temporaryRoot.'/meetup-race-'.getmypid().'-'.bin2hex(random_bytes(6));
    $filesystem = new Filesystem;
    $filesystem->ensureDirectoryExists($workingDirectory, 0770);
    $database = $workingDirectory.'/race.sqlite';
    touch($database);
    $environment = [
        'APP_ENV' => 'testing',
        'CACHE_STORE' => 'array',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => $database,
        'DB_TRANSACTION_MODE' => 'IMMEDIATE',
        'DB_URL' => false,
        'LARAVEL_STORAGE_PATH' => $workingDirectory.'/storage',
        'QUEUE_CONNECTION' => 'sync',
        'SESSION_DRIVER' => 'array',
    ];
    $repositoryRoot = dirname(__DIR__, 3);
    $workerPath = $repositoryRoot.'/tests/Fixtures/meetup_registration_race_worker.php';

    foreach (['app/private', 'app/public', 'framework/cache/data', 'framework/sessions', 'framework/testing', 'framework/views', 'logs'] as $directory) {
        $filesystem->ensureDirectoryExists($workingDirectory.'/storage/'.$directory, 0770);
    }

    try {
        $migrate = new Process(
            [PHP_BINARY, 'artisan', 'migrate:fresh', '--force', '--no-interaction'],
            $repositoryRoot,
            $environment,
        );
        $migrate->setTimeout(120);
        $migrate->run();
        expect($migrate->isSuccessful())->toBeTrue($migrate->getErrorOutput().$migrate->getOutput());

        $prepare = new Process(
            [PHP_BINARY, $workerPath, $database, 'prepare'],
            $repositoryRoot,
            $environment,
        );
        $prepare->setTimeout(45);
        $prepare->run();
        expect($prepare->isSuccessful())->toBeTrue($prepare->getErrorOutput().$prepare->getOutput());
        $fixture = json_decode($prepare->getOutput(), true, flags: JSON_THROW_ON_ERROR);

        $barrier = $workingDirectory.'/barrier';
        $workers = [];

        foreach ($fixture['emails'] as $index => $email) {
            $ready = $workingDirectory."/ready-{$index}";
            $process = new Process([
                PHP_BINARY,
                $workerPath,
                $database,
                'register',
                (string) $fixture['event_id'],
                $email,
                "meetup-capacity-race-000{$index}",
                $ready,
                $barrier,
            ], $repositoryRoot, $environment);
            $process->setTimeout(45);
            $process->start();
            $workers[] = [$process, $ready];
        }

        $deadline = microtime(true) + 15;

        do {
            $ready = collect($workers)->every(static fn (array $worker): bool => is_file($worker[1]));

            if (! $ready) {
                usleep(10_000);
            }
        } while (! $ready && microtime(true) < $deadline);

        expect($ready)->toBeTrue('Both meetup workers must reach the race barrier.');
        touch($barrier);

        $workerStatuses = [];

        foreach ($workers as [$process]) {
            $process->wait();
            expect($process->isSuccessful())->toBeTrue($process->getErrorOutput().$process->getOutput());
            $workerStatuses[] = json_decode(
                $process->getOutput(),
                true,
                flags: JSON_THROW_ON_ERROR,
            )['status'];
        }

        sort($workerStatuses);
        expect($workerStatuses)->toBe(['confirmed', 'waitlisted']);

        $pdo = new PDO('sqlite:'.$database);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $count = static fn (string $sql): int => (int) $pdo->query($sql)->fetchColumn();

        expect($count("select count(*) from forum_event_registrations where status = 'confirmed'"))->toBe(1)
            ->and($count("select count(*) from forum_event_registrations where status = 'waitlisted'"))->toBe(1)
            ->and($count("select count(*) from forum_event_participation_operations where status = 'completed'"))->toBe(2)
            ->and($count('select count(distinct active_scope_key) from forum_event_registrations'))->toBe(2)
            ->and($count('pragma foreign_key_check'))->toBe(0);
    } finally {
        $resolvedWorkingDirectory = realpath($workingDirectory);

        if ($resolvedWorkingDirectory !== false
            && dirname($resolvedWorkingDirectory) === $temporaryRoot
            && preg_match('/^meetup-race-[0-9]+-[a-f0-9]{12}$/', basename($resolvedWorkingDirectory))) {
            $filesystem->deleteDirectory($resolvedWorkingDirectory);
        }
    }
});
