<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Process;

test('the onboarding migration preserves a legacy user through apply rollback and reapply', function (): void {
    $result = Process::path(base_path())
        ->timeout(240)
        ->run([PHP_BINARY, 'scripts/verify-onboarding-migration.php']);

    expect($result->successful(), $result->errorOutput().$result->output())
        ->toBeTrue();

    $report = json_decode($result->output(), true, flags: JSON_THROW_ON_ERROR);

    expect($report)
        ->toBeArray()
        ->and($report['migration'])->toBe('2026_08_30_270000_create_user_onboardings_table')
        ->and($report['legacy_users'])->toBe(5)
        ->and($report['legacy_user_preserved_after_apply'])->toBeTrue()
        ->and($report['legacy_user_has_no_onboarding_row'])->toBeTrue()
        ->and($report['onboarding_table_removed_after_rollback'])->toBeTrue()
        ->and($report['legacy_user_preserved_after_rollback'])->toBeTrue()
        ->and($report['legacy_user_preserved_after_reapply'])->toBeTrue()
        ->and($report['legacy_user_has_no_onboarding_row_after_reapply'])->toBeTrue()
        ->and($report['first_apply_exit'])->toBe(0)
        ->and($report['rollback_exit'])->toBe(0)
        ->and($report['second_apply_exit'])->toBe(0);
});
