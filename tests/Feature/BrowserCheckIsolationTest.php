<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

test('browser verification proves its resolved connection and ignores an inherited database URL', function (): void {
    $hostileDatabase = sys_get_temp_dir().'/pawcircle-hostile-browser-'.bin2hex(random_bytes(8)).'.sqlite';
    File::put($hostileDatabase, 'sentinel');

    try {
        $result = Process::path(base_path())
            ->env(['DB_URL' => 'sqlite:///'.$hostileDatabase])
            ->timeout(30)
            ->run([PHP_BINARY, 'scripts/run-browser-check.php', 'a11y', '--assert-isolation']);
        $proof = json_decode($result->output(), true, flags: JSON_THROW_ON_ERROR);

        expect($result->successful())->toBeTrue()
            ->and($proof['resolved_database_path'])->toBe($proof['database_path'])
            ->and($proof['pdo_driver'])->toBe('sqlite')
            ->and($proof['database_url'])->toBeNull()
            ->and($proof['storage_path'])->not->toStartWith(base_path())
            ->and(File::exists($proof['storage_path']))->toBeFalse()
            ->and(File::get($hostileDatabase))->toBe('sentinel');
    } finally {
        File::delete($hostileDatabase);
    }
});

test('the long-running browser server cannot fill undrained output pipes', function (): void {
    $source = File::get(base_path('scripts/run-browser-check.php'));
    $disableOutput = strpos($source, '$server->disableOutput();');
    $start = strpos($source, '$server->start();');

    expect($disableOutput)->not->toBeFalse()
        ->and($start)->not->toBeFalse()
        ->and($disableOutput)->toBeLessThan($start)
        ->and($source)->not->toContain('$server->getErrorOutput()');
});
