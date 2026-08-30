<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

test('compliance matrix default command retains stdout behavior', function (): void {
    $result = Process::path(base_path())->run([
        PHP_BINARY,
        'scripts/generate-compliance-matrix.php',
    ]);

    expect($result->successful())->toBeTrue()
        ->and($result->output())->toBe(File::get(
            base_path('docs/requirements/compliance-matrix.md'),
        ));
});

test('compliance matrix check detects drift without modifying its target', function (): void {
    $directory = sys_get_temp_dir().'/pawcircle-compliance-matrix-'.bin2hex(random_bytes(8));
    File::makeDirectory($directory, 0700, true);

    try {
        $target = $directory.'/compliance-matrix.md';
        File::put($target, "# stale matrix\n");
        $before = hash_file('sha256', $target);

        $result = Process::path(base_path())->run([
            PHP_BINARY,
            'scripts/generate-compliance-matrix.php',
            '--check',
            '--target='.$target,
        ]);

        expect($result->exitCode())->toBe(1)
            ->and($result->errorOutput())->toContain('First difference at line 1')
            ->and(hash_file('sha256', $target))->toBe($before);
    } finally {
        File::deleteDirectory($directory);
    }
});

test('compliance matrix write atomically refreshes a target and preserves its permissions', function (): void {
    $directory = sys_get_temp_dir().'/pawcircle-compliance-matrix-'.bin2hex(random_bytes(8));
    File::makeDirectory($directory, 0700, true);

    try {
        $target = $directory.'/compliance-matrix.md';
        File::put($target, "# stale matrix\n");
        chmod($target, 0640);
        $expected = Process::path(base_path())
            ->run([PHP_BINARY, 'scripts/generate-compliance-matrix.php'])
            ->output();

        $write = Process::path(base_path())->run([
            PHP_BINARY,
            'scripts/generate-compliance-matrix.php',
            '--write',
            '--target='.$target,
        ]);
        clearstatcache(true, $target);

        expect($write->exitCode())->toBe(0)
            ->and(File::get($target))->toBe($expected)
            ->and(fileperms($target) & 0777)->toBe(0640);

        $check = Process::path(base_path())->run([
            PHP_BINARY,
            'scripts/generate-compliance-matrix.php',
            '--check',
            '--target='.$target,
        ]);

        expect($check->successful())->toBeTrue();
    } finally {
        File::deleteDirectory($directory);
    }
});

test('compliance matrix command rejects a symlink target', function (): void {
    $directory = sys_get_temp_dir().'/pawcircle-compliance-matrix-'.bin2hex(random_bytes(8));
    File::makeDirectory($directory, 0700, true);

    try {
        $generated = Process::path(base_path())
            ->run([PHP_BINARY, 'scripts/generate-compliance-matrix.php'])
            ->output();
        $realTarget = $directory.'/real-matrix.md';
        $symlinkTarget = $directory.'/linked-matrix.md';
        File::put($realTarget, $generated);
        symlink($realTarget, $symlinkTarget);
        $before = hash_file('sha256', $realTarget);

        $check = Process::path(base_path())->run([
            PHP_BINARY,
            'scripts/generate-compliance-matrix.php',
            '--check',
            '--target='.$symlinkTarget,
        ]);

        expect($check->exitCode())->toBe(1)
            ->and($check->errorOutput())->toContain('Refusing symlink')
            ->and(is_link($symlinkTarget))->toBeTrue()
            ->and(hash_file('sha256', $realTarget))->toBe($before);
    } finally {
        File::deleteDirectory($directory);
    }
});
