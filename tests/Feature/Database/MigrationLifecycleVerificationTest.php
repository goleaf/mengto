<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

test('every migration declares a reversible schema builder boundary', function () {
    $violations = [];

    foreach (File::files(database_path('migrations')) as $migration) {
        $source = $migration->getContents();

        if (! str_contains($source, 'public function up(): void')) {
            $violations[] = $migration->getFilename().': missing typed up';
        }

        if (! str_contains($source, 'public function down(): void')) {
            $violations[] = $migration->getFilename().': missing typed down';
        }

        foreach ([
            'DB::raw(',
            'DB::select(',
            'DB::statement(',
            'DB::unprepared(',
        ] as $forbidden) {
            if (str_contains($source, $forbidden)) {
                $violations[] = $migration->getFilename().": {$forbidden}";
            }
        }
    }

    expect($violations)->toBe([]);
});

test('every migration rolls back and reapplies in an isolated database', function () {
    $result = Process::path(base_path())
        ->timeout(180)
        ->run([PHP_BINARY, 'scripts/verify-migration-cycle.php']);

    expect($result->successful(), $result->errorOutput().$result->output())
        ->toBeTrue();

    $report = json_decode($result->output(), true, flags: JSON_THROW_ON_ERROR);
    $migrationFiles = count(File::files(database_path('migrations')));

    expect($report)
        ->toBeArray()
        ->and($report['migration_files'])->toBe($migrationFiles)
        ->and($report['first_apply_migrations'])->toBe($migrationFiles)
        ->and($report['remaining_after_rollback'])->toBe(0)
        ->and($report['second_apply_migrations'])->toBe($migrationFiles)
        ->and($report['first_ledger_matches_files'])->toBeTrue()
        ->and($report['second_ledger_matches_files'])->toBeTrue()
        ->and($report['users_before_repeat'])->toBe(5)
        ->and($report['users_after_repeat'])->toBe(5);
});
