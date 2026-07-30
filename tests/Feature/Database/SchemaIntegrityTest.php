<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

test('every foreign key is covered by a leading index', function () {
    $missingIndexes = [];

    foreach (Schema::getTableListing() as $table) {
        $indexes = Schema::getIndexes($table);

        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            $columns = $foreignKey['columns'];
            $covered = collect($indexes)->contains(
                static fn (array $index): bool => array_slice(
                    $index['columns'],
                    0,
                    count($columns),
                ) === $columns,
            );

            if (! $covered) {
                $missingIndexes[] = $table.'.'.implode('+', $columns);
            }
        }
    }

    expect($missingIndexes)->toBe([]);
});

test('the foreign key index migration is reversible', function () {
    $migration = require database_path(
        'migrations/2026_07_30_180000_add_missing_foreign_key_indexes.php',
    );

    $migration->down();

    expect(Schema::hasIndex(
        'availability_slots',
        'availability_slots_service_id_fk_idx',
    ))->toBeFalse();

    $migration->up();

    expect(Schema::hasIndex(
        'availability_slots',
        'availability_slots_service_id_fk_idx',
    ))->toBeTrue();
});
