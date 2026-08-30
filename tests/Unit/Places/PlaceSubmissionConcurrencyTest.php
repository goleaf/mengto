<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

test('simultaneous matching submissions preserve both records and create a deterministic candidate link', function () {
    $temporaryRoot = realpath(sys_get_temp_dir());
    expect($temporaryRoot)->not->toBeFalse();
    $workingDirectory = $temporaryRoot.'/pla-p06-race-'.getmypid().'-'.bin2hex(random_bytes(6));
    $filesystem = new Filesystem;
    $filesystem->ensureDirectoryExists($workingDirectory, 0770);
    $database = $workingDirectory.'/race.sqlite';
    touch($database);
    $environment = [
        'APP_ENV' => 'testing',
        'CACHE_STORE' => 'array',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => $database,
        'DB_URL' => false,
        'LARAVEL_STORAGE_PATH' => $workingDirectory.'/storage',
        'QUEUE_CONNECTION' => 'sync',
        'SESSION_DRIVER' => 'array',
    ];
    $repositoryRoot = dirname(__DIR__, 3);

    foreach (['app/private', 'app/public', 'framework/cache/data', 'framework/sessions', 'framework/testing', 'framework/views', 'logs'] as $directory) {
        $filesystem->ensureDirectoryExists($workingDirectory.'/storage/'.$directory, 0770);
    }

    try {
        $prepare = new Process(
            [PHP_BINARY, 'artisan', 'migrate:fresh', '--seed', '--force', '--no-interaction'],
            $repositoryRoot,
            $environment,
        );
        $prepare->setTimeout(120);
        $prepare->run();
        expect($prepare->isSuccessful())->toBeTrue($prepare->getErrorOutput().$prepare->getOutput());

        $barrier = $workingDirectory.'/barrier';
        $workers = [];
        $identities = [
            ['user@example.com', '20000000-0000-4000-8000-000000000001'],
            ['lithuanian@example.test', '20000000-0000-4000-8000-000000000002'],
        ];

        foreach ($identities as $index => [$email, $operationKey]) {
            $ready = $workingDirectory."/ready-{$index}";
            $process = new Process([
                PHP_BINARY,
                $repositoryRoot.'/tests/Fixtures/place_submission_race_worker.php',
                $database,
                $email,
                $operationKey,
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

        expect($ready)->toBeTrue('Both place-submission race workers must reach the barrier.');
        touch($barrier);

        foreach ($workers as [$process]) {
            $process->wait();
            expect($process->isSuccessful())->toBeTrue($process->getErrorOutput().$process->getOutput());
            expect(json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR))
                ->toHaveKeys(['id', 'status']);
        }

        $pdo = new PDO('sqlite:'.$database);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $count = static fn (string $sql): int => (int) $pdo->query($sql)->fetchColumn();

        expect($count("select count(*) from place_submissions where name = 'Concurrent Community Clinic'"))->toBe(2)
            ->and($count("select count(*) from places where name = 'Concurrent Community Clinic'"))->toBe(0)
            ->and($count("select count(*) from place_submission_events where idempotency_key like 'submit:%:20000000-%'"))->toBe(2)
            ->and($count('select count(*) from place_duplicate_candidates where candidate_submission_id is not null'))->toBeGreaterThanOrEqual(1)
            ->and($count("select count(*) from place_submission_identity_locks where identity_hash in (select identity_hash from place_submissions where name = 'Concurrent Community Clinic')"))->toBe(1)
            ->and($count('pragma foreign_key_check'))->toBe(0);
    } finally {
        $resolvedWorkingDirectory = realpath($workingDirectory);

        if ($resolvedWorkingDirectory !== false
            && dirname($resolvedWorkingDirectory) === $temporaryRoot
            && preg_match('/^pla-p06-race-[0-9]+-[a-f0-9]{12}$/', basename($resolvedWorkingDirectory))) {
            $filesystem->deleteDirectory($resolvedWorkingDirectory);
        }
    }
});
