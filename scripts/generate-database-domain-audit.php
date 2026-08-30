<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RepresentativeModelManifest;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

const AUDIT_OUTPUT = 'docs/audits/database-domain-audit.md';
const AUDIT_FAKER_SEED = 260830;
const AUDIT_TEMP_PREFIX = 'pawcircle-database-domain-audit-';
const AUDIT_TARGET_USER_ACTOR_KEY = 'mia-carter';
const AUDIT_TARGET_USER_EMAIL = 'user@example.com';

/** @param list<string> $argv */
function runDatabaseDomainAudit(array $argv): int
{
    $root = dirname(__DIR__);
    $arguments = array_slice($argv, 1);

    if (count($arguments) !== 1 || ! in_array($arguments[0], ['--write', '--check'], true)) {
        fwrite(STDERR, "Usage: php scripts/generate-database-domain-audit.php --write|--check\n");

        return 2;
    }

    $temporaryRoot = realpath(sys_get_temp_dir());

    if ($temporaryRoot === false) {
        throw new RuntimeException('The system temporary directory is unavailable.');
    }

    $runtimeDirectory = $temporaryRoot.'/'.AUDIT_TEMP_PREFIX.bin2hex(random_bytes(8));
    $database = $runtimeDirectory.'/audit.sqlite';
    $configCache = $runtimeDirectory.'/config.php';
    $storageDirectory = $runtimeDirectory.'/storage';
    $compiledViewsDirectory = $runtimeDirectory.'/views';

    assertAuditRuntimePath($runtimeDirectory, $temporaryRoot);

    if (! mkdir($runtimeDirectory, 0700)) {
        throw new RuntimeException('Unable to create the isolated audit runtime.');
    }

    $auditExitCode = 0;
    $auditBootstrapped = false;

    try {
        if (! mkdir($storageDirectory, 0700)
            || ! mkdir($compiledViewsDirectory, 0700)
            || ! touch($database)) {
            throw new RuntimeException('Unable to initialize the isolated audit runtime.');
        }

        setAuditEnvironment('APP_ENV', 'testing');
        setAuditEnvironment('APP_CONFIG_CACHE', $configCache);
        setAuditEnvironment('EMAIL_VERIFICATION_ENABLED', 'true');
        setAuditEnvironment('DB_CONNECTION', 'sqlite');
        setAuditEnvironment('DB_DATABASE', $database);
        setAuditEnvironment('CACHE_STORE', 'array');
        setAuditEnvironment('SESSION_DRIVER', 'array');
        setAuditEnvironment('QUEUE_CONNECTION', 'sync');
        setAuditEnvironment('MAIL_MAILER', 'array');
        setAuditEnvironment('SCOUT_DRIVER', 'null');
        setAuditEnvironment('VIEW_COMPILED_PATH', $compiledViewsDirectory);

        require $root.'/vendor/autoload.php';

        $application = require $root.'/bootstrap/app.php';
        $application->make(Kernel::class)->bootstrap();
        $auditBootstrapped = true;

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.url', null);
        config()->set('database.connections.sqlite.database', $database);
        config()->set('filesystems.disks.local.root', $storageDirectory);
        config()->set('filesystems.disks.public.root', $storageDirectory.'/public');
        DB::purge('sqlite');
        fake()->seed(AUDIT_FAKER_SEED);

        if (config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== $database
            || dirname($database) !== $runtimeDirectory
            || is_file($configCache)) {
            throw new RuntimeException('The isolated audit database configuration assertion failed.');
        }

        runAuditCommand('migrate', [
            '--database' => 'sqlite',
            '--force' => true,
            '--no-interaction' => true,
        ]);
        runAuditCommand('db:seed', [
            '--database' => 'sqlite',
            '--force' => true,
            '--no-interaction' => true,
        ]);

        $document = buildAuditDocument($root);
        $outputPath = $root.'/'.AUDIT_OUTPUT;

        if ($arguments[0] === '--write') {
            if (file_put_contents($outputPath, $document) === false) {
                throw new RuntimeException('Unable to write '.AUDIT_OUTPUT.'.');
            }

            fwrite(STDOUT, 'Wrote '.AUDIT_OUTPUT.PHP_EOL);
        } else {
            $current = is_file($outputPath) ? file_get_contents($outputPath) : false;

            if ($current === false || ! hash_equals(hash('sha256', $document), hash('sha256', $current))) {
                fwrite(STDERR, AUDIT_OUTPUT." is stale. Run the generator with --write.\n");

                if ($current !== false) {
                    reportFirstAuditDifference($current, $document);
                }

                $auditExitCode = 1;
            } else {
                fwrite(STDOUT, AUDIT_OUTPUT." is current.\n");
            }
        }
    } finally {
        if ($auditBootstrapped) {
            DB::disconnect('sqlite');
        }

        removeAuditRuntime($runtimeDirectory, $temporaryRoot);
    }

    return $auditExitCode;
}

if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    try {
        exit(runDatabaseDomainAudit($_SERVER['argv'] ?? []));
    } catch (Throwable $throwable) {
        fwrite(STDERR, 'Database domain audit failed: '.$throwable->getMessage().PHP_EOL);
        exit(1);
    }
}

function reportFirstAuditDifference(string $current, string $generated): void
{
    $currentLines = explode("\n", $current);
    $generatedLines = explode("\n", $generated);
    $lastLine = max(count($currentLines), count($generatedLines));

    for ($line = 0; $line < $lastLine; $line++) {
        $currentLine = $currentLines[$line] ?? '[missing]';
        $generatedLine = $generatedLines[$line] ?? '[missing]';

        if ($currentLine === $generatedLine) {
            continue;
        }

        fwrite(STDERR, 'First difference at line '.($line + 1).":\n");
        fwrite(STDERR, "  committed: {$currentLine}\n");
        fwrite(STDERR, "  generated: {$generatedLine}\n");

        return;
    }
}

/** @param array<string, int|string|bool> $parameters */
function runAuditCommand(string $command, array $parameters): void
{
    $exitCode = Artisan::call($command, $parameters);

    if ($exitCode !== 0) {
        throw new RuntimeException(
            "{$command} failed with exit code {$exitCode}: ".Artisan::output(),
        );
    }
}

function buildAuditDocument(string $root): string
{
    $migrationFiles = glob($root.'/database/migrations/*.php') ?: [];
    $seederFiles = array_values(array_filter(
        glob($root.'/database/seeders/*.php') ?: [],
        static fn (string $path): bool => basename($path) !== 'RepresentativeModelManifest.php',
    ));
    sort($migrationFiles);
    sort($seederFiles);

    $migrationSources = fileSources($migrationFiles);
    $seederSources = fileSources($seederFiles);
    $migrationInventory = migrationInventory($migrationSources);
    $migrationIndex = migrationIndex($migrationInventory);
    $tables = Schema::getTables();
    usort($tables, static fn (array $left, array $right): int => $left['name'] <=> $right['name']);

    $tableSchemas = [];
    $columnCount = 0;
    $foreignKeyCount = 0;
    $indexCount = 0;
    $uniqueIndexCount = 0;

    $checkDefinitions = DB::table('sqlite_master')
        ->where('type', 'table')
        ->whereNotNull('sql')
        ->pluck('sql', 'name')
        ->map(static fn (mixed $sql): string => (string) $sql)
        ->all();
    $checkCount = 0;

    foreach ($tables as $tableDefinition) {
        $table = $tableDefinition['name'];
        $columns = Schema::getColumns($table);
        $foreignKeys = Schema::getForeignKeys($table);
        $indexes = Schema::getIndexes($table);
        $tableCheckCount = preg_match_all('/\bcheck\s*\(/i', $checkDefinitions[$table] ?? '');
        $tableCheckCount = $tableCheckCount === false ? 0 : $tableCheckCount;
        $tableMigrations = migrationsForTable($table, $migrationIndex, $migrationSources);

        $columnCount += count($columns);
        $foreignKeyCount += count($foreignKeys);
        $indexCount += count($indexes);
        $uniqueIndexCount += count(array_filter(
            $indexes,
            static fn (array $index): bool => $index['unique'] && ! $index['primary'],
        ));
        $checkCount += $tableCheckCount;

        $tableSchemas[$table] = [
            'columns' => $columns,
            'foreign_keys' => $foreignKeys,
            'indexes' => $indexes,
            'checks' => $tableCheckCount,
            'migrations' => $tableMigrations,
            'column_declarations' => sourceColumnDeclarations(
                $table,
                $columns,
                $tableMigrations,
                $migrationSources,
            ),
        ];
    }

    $modelClasses = RepresentativeModelManifest::classes();
    sort($modelClasses);
    $discoveredModelClasses = persistentApplicationModelClasses($root);

    if ($modelClasses !== $discoveredModelClasses) {
        $missingFromManifest = array_values(array_diff($discoveredModelClasses, $modelClasses));
        $staleManifestEntries = array_values(array_diff($modelClasses, $discoveredModelClasses));

        throw new RuntimeException(
            'The representative manifest does not match persistent model discovery. '
            .'Missing: '.formatList($missingFromManifest).'. '
            .'Stale: '.formatList($staleManifestEntries).'.',
        );
    }

    $modelRows = [];
    $modelTables = [];
    $modelClassesByTable = [];
    $relationshipCount = 0;
    $enumCastCount = 0;
    $missingFactories = [];
    $underfilledModels = [];
    $modelsWithoutMigration = [];

    foreach ($modelClasses as $modelClass) {
        $model = new $modelClass;
        $table = $model->getTable();
        $modelTables[$table][] = class_basename($modelClass);
        $modelClassesByTable[$table] = $modelClass;
        $schema = $tableSchemas[$table] ?? null;

        if ($schema === null) {
            throw new RuntimeException("Model {$modelClass} maps to missing table {$table}.");
        }

        $factoryPath = 'database/factories/'.class_basename($modelClass).'Factory.php';
        $factoryExists = is_file($root.'/'.$factoryPath);
        $relationships = declaredRelationships($modelClass);
        $casts = modelCasts($model);
        $count = $model->newQueryWithoutScopes()->count();

        $relationshipCount += count($relationships);
        $enumCastCount += count(array_filter(
            $casts,
            static fn (string $cast): bool => str_ends_with($cast, ' [enum]'),
        ));

        if (! $factoryExists) {
            $missingFactories[] = $modelClass;
        }

        if ($count < RepresentativeModelManifest::TARGET_COUNT) {
            $underfilledModels[$modelClass] = $count;
        }

        if ($schema['migrations'] === []) {
            $modelsWithoutMigration[] = $modelClass;
        }

        $modelRows[] = [
            'model' => class_basename($modelClass),
            'table' => $table,
            'primary_key' => modelPrimaryKey($model),
            'factory' => $factoryExists ? $factoryPath : 'Missing',
            'migrations' => formatList($schema['migrations']),
            'columns' => columnContract(
                $schema['columns'],
                $schema['column_declarations'],
                polymorphicColumnsForModel($modelClass),
            ),
            'indexes' => indexContract($schema['indexes']),
            'unique' => uniqueContract($schema['indexes']),
            'foreign_keys' => foreignKeyContract($schema['foreign_keys']),
            'checks' => (string) $schema['checks'],
            'casts' => formatMap($casts),
            'relationships' => formatMap($relationships),
            'seeders' => formatList(seedersForModel($modelClass, $seederSources)),
            'seeded_count' => $count.' observed (target ≥'
                .RepresentativeModelManifest::TARGET_COUNT.': '
                .($count >= RepresentativeModelManifest::TARGET_COUNT ? 'met' : 'not met').')',
        ];
    }

    assertModelColumnSourceCoverage($modelClassesByTable, $tableSchemas);

    $orphanCount = seededOrphanCount($tableSchemas);
    $targetUser = DB::table('users')->where('email', AUDIT_TARGET_USER_EMAIL)->first();
    $nonModelTables = array_values(array_diff(array_keys($tableSchemas), array_keys($modelTables)));
    sort($nonModelTables);
    $allModelsMeetTarget = $underfilledModels === [];
    $userInverseExclusions = userInverseExclusions($tableSchemas, $modelClassesByTable);

    assertAuditIntegrity(
        $migrationFiles,
        $migrationInventory,
        array_keys($tableSchemas),
        $missingFactories,
        $underfilledModels,
        $orphanCount,
        $targetUser === null ? null : (string) $targetUser->actor_key,
    );

    $lines = [
        '# Database Domain Audit',
        '',
        'Generated by `php scripts/generate-database-domain-audit.php --write`. The generator always migrates and root-seeds a newly created SQLite database under the system temporary directory; it asserts the exact connection before any database command and removes only its validated temporary runtime.',
        '',
        '## Audit snapshot',
        '',
        '| Measure | Observed |',
        '| --- | ---: |',
        '| Migration files | '.count($migrationFiles).' |',
        '| Database tables | '.count($tables).' |',
        "| Columns | {$columnCount} |",
        "| Foreign keys | {$foreignKeyCount} |",
        "| Indexes, including primary indexes | {$indexCount} |",
        "| Non-primary unique indexes | {$uniqueIndexCount} |",
        "| SQLite check constraints | {$checkCount} |",
        '| Persistent application models | '.count($modelRows).' |',
        '| Concrete model factories | '.(count($modelRows) - count($missingFactories)).' |',
        "| Declared Eloquent relationships | {$relationshipCount} |",
        '| Explicit User-role inverse exclusions | '.count($userInverseExclusions).' |',
        "| Enum-backed model casts | {$enumCastCount} |",
        '| Root-seeded models meeting the 10-record target | '.($allModelsMeetTarget ? count($modelRows) : count($modelRows) - count($underfilledModels)).' |',
        "| Seeded foreign-key orphans | {$orphanCount} |",
        '',
        'The seed snapshot includes `'.AUDIT_TARGET_USER_EMAIL.'` with actor key `'.escapeInline((string) ($targetUser->actor_key ?? '')).'`; its observed user row exists: **'.($targetUser === null ? 'no' : 'yes').'**.',
        '',
        'Column contracts include the runtime SQL type, nullability, observed non-null default, auto-increment or generation metadata, collation/comment when present, table-scoped chronological `up()` schema-builder declarations (including length, precision/scale, and enum arguments where declared), and UUID, ULID, model-backed polymorphic, or soft-delete classifications. Unique and index entries show exact column order. Foreign-key entries record update/delete actions.',
        '',
        '## Persistent model matrix',
        '',
        '| Model | Table | Primary key | Factory | Migration(s) | Full column contract | Indexes | Unique constraints | Foreign keys | Checks | Casts and enums | Full relationship contract | Seeder references | Fresh root-seed evidence |',
        '| --- | --- | --- | --- | --- | --- | --- | --- | --- | ---: | --- | --- | --- | ---: |',
    ];

    foreach ($modelRows as $row) {
        $lines[] = '| '.implode(' | ', array_map('escapeCell', $row)).' |';
    }

    $lines = [
        ...$lines,
        '',
        '## Non-model, pivot, and framework table catalog',
        '',
        'These tables are deliberately listed separately so every migrated table remains auditable even when it is a pivot, queue/cache/auth infrastructure table, or the migration ledger.',
        '',
        '| Table | Migration(s) | Full column contract | Indexes | Unique constraints | Foreign keys | Checks | Fresh row count |',
        '| --- | --- | --- | --- | --- | --- | ---: | ---: |',
    ];

    foreach ($nonModelTables as $table) {
        $schema = $tableSchemas[$table];
        $lines[] = '| '.implode(' | ', array_map('escapeCell', [
            $table,
            formatList($schema['migrations'] === [] && $table === 'migrations'
                ? ['Laravel migration repository']
                : $schema['migrations']),
            columnContract($schema['columns'], $schema['column_declarations']),
            indexContract($schema['indexes']),
            uniqueContract($schema['indexes']),
            foreignKeyContract($schema['foreign_keys']),
            (string) $schema['checks'],
            (string) DB::table($table)->count(),
        ])).' |';
    }

    $lines = [
        ...$lines,
        '',
        '## Migration inventory',
        '',
        'Every migration file is listed exactly once. Affected tables include tables reached through constant-driven index loops as well as literal `Schema::create`, `Schema::table`, and `Schema::rename` calls.',
        '',
        '| Migration | Affected table(s) |',
        '| --- | --- |',
    ];

    foreach ($migrationInventory as $migration => $affectedTables) {
        $lines[] = '| `'.escapeCell($migration).'` | '.escapeCell(formatList($affectedTables)).' |';
    }

    $lines = [
        ...$lines,
        '',
        '## Explicit User-role inverse exclusions',
        '',
        'Every model-backed child foreign key to `users` has a child-side `BelongsTo`. The following parent inverses are intentionally not added to the already broad `User` aggregate: they are role-specific audit, moderation, reviewer, actor, or lifecycle provenance edges that are queried from their bounded child domain. The non-model moderation-case/report pivot provenance edge is listed separately in the same schema-qualified set.',
        '',
    ];

    foreach ($userInverseExclusions as $exclusion) {
        $lines[] = '- `'.escapeInline($exclusion).'` — child-to-user traversal is canonical; no general-purpose parent collection is exposed.';
    }

    $lines = [
        ...$lines,
        '',
        '## Findings and remediations',
        '',
        '- The manifest and runtime discovery agree on **'.count($modelRows).'** persistent application models. Every model maps to a migrated table.',
        '- Factory coverage is **'.(count($modelRows) - count($missingFactories)).'/'.count($modelRows).'**. '.($missingFactories === [] ? 'No factory exemption is required.' : 'Missing: '.formatList($missingFactories).'.'),
        '- A clean root seed produced at least **'.RepresentativeModelManifest::TARGET_COUNT.'** rows for every persistent model. '.($underfilledModels === [] ? 'No model is underfilled.' : 'Underfilled: '.formatMap($underfilledModels).'.'),
        '- The schema-derived foreign-key audit found **'.$orphanCount.'** orphaned seeded rows.',
        '- The model/schema reconciliation found **'.count($modelsWithoutMigration).'** models without a source migration reference. '.($modelsWithoutMigration === [] ? 'No unresolved model-to-migration mapping remains.' : 'Unresolved: '.formatList($modelsWithoutMigration).'.'),
        '- Relationship remediation aligned child and appropriate parent inverses with migrated foreign keys, preserved explicit polymorphic relation types, and attached pivot metadata where the schema requires it. Each parameterless relation records its related model/table, key contract, and pivot or morph metadata; intentionally omitted `User` role inverses are schema-qualified above.',
        '- Factory remediation supplies every concrete model, uses schema-valid enum/cast values, coherent owners and parents, representative nullable fields, ordered lifecycle dates, and non-recursive opt-in graph helpers.',
        '- Seeder remediation uses the manifest-driven bounded top-up, dependency-ordered domain seeders, metadata-complete pivots, and the deterministic `user@example.com` identity without truncating existing records.',
        '- Deterministic identity, relationship graph, pivot metadata, field coverage, uniqueness, repeat seeding, and bounded record volume are verified by `tests/Feature/Database/CompleteDatabaseSeederTest.php` and `tests/Feature/Database/SeededFieldCoverageTest.php`.',
        '- Factory persistence, helper states, model serialization privacy, owner coherence, relationship integrity, aggregate recalculation, and domain-state chronology are verified by the focused tests under `tests/Feature/Database`.',
        '- Fresh migration/seed lifecycle and rollback/reapply behavior are verified by `scripts/verify-fresh-database.php` and `scripts/verify-migration-cycle.php`; neither script uses the configured application database.',
        '',
        '## Regeneration gate',
        '',
        'Run `php scripts/generate-database-domain-audit.php --check`. The check performs the same isolated migration, root seed, reflection, schema inspection, completeness assertions, and byte-for-byte comparison. It exits nonzero before comparison when a model factory is missing, a model is below target, a seeded foreign key is orphaned, the deterministic user identity is absent or incorrect, any migration is absent/unmapped, or any runtime table lacks a reverse migration mapping.',
        '',
    ];

    return implode("\n", $lines);
}

/**
 * @param  list<string>  $paths
 * @return array<string, string>
 */
function fileSources(array $paths): array
{
    $sources = [];

    foreach ($paths as $path) {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read {$path}.");
        }

        $sources[$path] = $contents;
    }

    return $sources;
}

/**
 * @param  array<string, string>  $migrationSources
 * @return array<string, list<string>>
 */
function migrationInventory(array $migrationSources): array
{
    $inventory = [];

    foreach ($migrationSources as $path => $source) {
        preg_match_all(
            '/Schema::(?:create|table|rename)\(\s*([\'\"])([^\'\"]+)\1/',
            $source,
            $literalMatches,
        );
        $tables = $literalMatches[2];

        if (preg_match('/Schema::(?:create|table|rename)\(\s*\$table\b/', $source) === 1
            && preg_match('/\bconst\s+INDEXES\s*=\s*\[(.*?)\n\s*\];/s', $source, $constantMatch) === 1) {
            preg_match_all(
                '/^\s*[\'\"]([a-z0-9_]+)[\'\"]\s*=>\s*\[/m',
                $constantMatch[1],
                $dynamicMatches,
            );
            $tables = [...$tables, ...$dynamicMatches[1]];
        }

        preg_match_all('/^use\s+(App\\\\Models\\\\[A-Za-z0-9_]+);$/m', $source, $modelMatches);

        foreach ($modelMatches[1] as $modelClass) {
            $shortName = substr($modelClass, (int) strrpos($modelClass, '\\') + 1);

            if (preg_match('/\b'.preg_quote($shortName, '/').'::query\s*\(/', $source) !== 1
                || ! class_exists($modelClass)
                || ! is_subclass_of($modelClass, Model::class)) {
                continue;
            }

            $tables[] = (new $modelClass)->getTable();
        }

        $tables = array_values(array_unique($tables));
        sort($tables);
        $inventory[basename($path)] = $tables;
    }

    ksort($inventory);

    return $inventory;
}

/**
 * @param  array<string, list<string>>  $migrationInventory
 * @return array<string, list<string>>
 */
function migrationIndex(array $migrationInventory): array
{
    $index = [];

    foreach ($migrationInventory as $migration => $tables) {
        foreach ($tables as $table) {
            $index[$table][] = $migration;
        }
    }

    foreach ($index as &$migrations) {
        $migrations = array_values(array_unique($migrations));
        sort($migrations);
    }
    unset($migrations);

    ksort($index);

    return $index;
}

/**
 * @param  array<string, list<string>>  $migrationIndex
 * @param  array<string, string>  $migrationSources
 * @return list<string>
 */
function migrationsForTable(
    string $table,
    array $migrationIndex,
    array $migrationSources,
): array {
    $migrations = $migrationIndex[$table] ?? [];

    if ($migrations === []) {
        $quotedTable = preg_quote($table, '/');

        foreach ($migrationSources as $path => $source) {
            if (preg_match('/[\'\"]'.$quotedTable.'[\'\"]/', $source) === 1) {
                $migrations[] = basename($path);
            }
        }
    }

    $migrations = array_values(array_unique($migrations));
    sort($migrations);

    return $migrations;
}

/**
 * @param  list<string>  $migrationFiles
 * @param  array<string, list<string>>  $migrationInventory
 * @param  list<string>  $knownTables
 * @param  list<string>  $missingFactories
 * @param  array<string, int>  $underfilledModels
 */
function assertAuditIntegrity(
    array $migrationFiles,
    array $migrationInventory,
    array $knownTables,
    array $missingFactories,
    array $underfilledModels,
    int $orphanCount,
    ?string $targetUserActorKey,
): void {
    $expectedMigrations = array_map('basename', $migrationFiles);
    $inventoriedMigrations = array_keys($migrationInventory);
    sort($expectedMigrations);
    sort($inventoriedMigrations);
    $missingMigrations = array_values(array_diff($expectedMigrations, $inventoriedMigrations));
    $unexpectedMigrations = array_values(array_diff($inventoriedMigrations, $expectedMigrations));

    if ($missingMigrations !== [] || $unexpectedMigrations !== []) {
        throw new RuntimeException(
            'Migration inventory is incomplete. Missing: '.formatList($missingMigrations)
            .'. Unexpected: '.formatList($unexpectedMigrations).'.',
        );
    }

    $unmappedMigrations = array_keys(array_filter(
        $migrationInventory,
        static fn (array $tables): bool => $tables === [],
    ));

    if ($unmappedMigrations !== []) {
        throw new RuntimeException(
            'Migration inventory contains unmapped files: '.formatList($unmappedMigrations).'.',
        );
    }

    $knownTableLookup = array_fill_keys($knownTables, true);
    $unknownMappings = [];

    foreach ($migrationInventory as $migration => $tables) {
        foreach ($tables as $table) {
            if (! isset($knownTableLookup[$table])) {
                $unknownMappings[] = "{$migration}:{$table}";
            }
        }
    }

    if ($unknownMappings !== []) {
        throw new RuntimeException(
            'Migration inventory references unknown tables: '.formatList($unknownMappings).'.',
        );
    }

    $mappedTables = array_values(array_unique(array_merge(...array_values($migrationInventory))));
    $unmappedKnownTables = array_values(array_diff($knownTables, $mappedTables, ['migrations']));

    if ($unmappedKnownTables !== []) {
        throw new RuntimeException(
            'Runtime tables without a migration mapping: '.formatList($unmappedKnownTables).'.',
        );
    }

    if ($missingFactories !== []) {
        throw new RuntimeException(
            'Missing model factories: '.formatList($missingFactories).'.',
        );
    }

    if ($underfilledModels !== []) {
        throw new RuntimeException(
            'Models below the representative seed target: '.formatMap($underfilledModels).'.',
        );
    }

    if ($orphanCount !== 0) {
        throw new RuntimeException("Seeded foreign-key orphans: {$orphanCount}.");
    }

    if ($targetUserActorKey !== AUDIT_TARGET_USER_ACTOR_KEY) {
        throw new RuntimeException(
            AUDIT_TARGET_USER_EMAIL.' must exist with actor key '.AUDIT_TARGET_USER_ACTOR_KEY.'.',
        );
    }
}

/** @return list<class-string<Model>> */
function persistentApplicationModelClasses(string $root): array
{
    $appDirectory = $root.'/app';
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($appDirectory, FilesystemIterator::SKIP_DOTS),
    );
    $models = [];

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $relativePath = substr($file->getPathname(), strlen($appDirectory) + 1, -4);
        $class = 'App\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

        if (! is_subclass_of($class, Model::class)) {
            continue;
        }

        $reflection = new ReflectionClass($class);

        if (! $reflection->isAbstract()) {
            /** @var class-string<Model> $class */
            $models[] = $class;
        }
    }

    $models = array_values(array_unique($models));
    sort($models);

    return $models;
}

/**
 * @param  class-string<Model>  $modelClass
 * @return array<string, string>
 */
function declaredRelationships(string $modelClass): array
{
    $reflection = new ReflectionClass($modelClass);
    $relationships = [];
    $model = new $modelClass;

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->getDeclaringClass()->getName() !== $modelClass || $method->isStatic()) {
            continue;
        }

        $relationType = relationReturnType($method);

        if ($relationType === null) {
            $source = methodSource($method);

            if (preg_match('/->(belongsTo|hasOne|hasMany|belongsToMany|hasOneThrough|hasManyThrough|morphTo|morphOne|morphMany|morphToMany|morphedByMany)\s*\(/', $source, $match) === 1) {
                $relationType = $match[1].' (inferred)';
            }
        }

        if ($relationType !== null) {
            if ($method->getNumberOfRequiredParameters() > 0) {
                $relationships[$method->getName()] = $relationType.' [parameterized helper; not invoked]';

                continue;
            }

            $relation = $method->invoke($model);

            if (! $relation instanceof Relation) {
                throw new RuntimeException(
                    "{$modelClass}::{$method->getName()} declares a relation but returned a non-relation value.",
                );
            }

            $relationships[$method->getName()] = relationshipContract($relation);
        }
    }

    ksort($relationships);

    return $relationships;
}

function relationshipContract(Relation $relation): string
{
    if ($relation instanceof MorphTo) {
        $parts = [
            class_basename($relation),
            'related=dynamic',
            'related_table=dynamic',
        ];
    } else {
        $related = $relation->getRelated();
        $parts = [
            class_basename($relation),
            'related='.$related::class,
            'related_table='.$related->getTable(),
        ];
    }
    $accessors = [
        'foreign_key' => 'getForeignKeyName',
        'local_key' => 'getLocalKeyName',
        'owner_key' => 'getOwnerKeyName',
        'foreign_pivot_key' => 'getForeignPivotKeyName',
        'related_pivot_key' => 'getRelatedPivotKeyName',
        'parent_key' => 'getParentKeyName',
        'related_key' => 'getRelatedKeyName',
        'first_key' => 'getFirstKeyName',
        'second_local_key' => 'getSecondLocalKeyName',
        'morph_type' => 'getMorphType',
        'morph_class' => 'getMorphClass',
    ];

    foreach ($accessors as $label => $accessor) {
        if (! method_exists($relation, $accessor)) {
            continue;
        }

        $value = $relation->{$accessor}();

        if (is_scalar($value) && (string) $value !== '') {
            $parts[] = $label.'='.(string) $value;
        }
    }

    if ($relation instanceof MorphTo) {
        $targets = morphToTargetClasses($relation);
        $parts[] = 'morph_targets='.formatList($targets);
        $morphMap = array_filter(
            Relation::morphMap() ?: [],
            static fn (string $class): bool => in_array($class, $targets, true),
        );

        if ($morphMap !== []) {
            $parts[] = 'morph_map='.formatMap($morphMap);
        }
    }

    if (method_exists($relation, 'getTable')) {
        $parts[] = 'pivot_table='.(string) $relation->getTable();
    }

    if (method_exists($relation, 'getPivotColumns')) {
        $parts[] = 'pivot_columns='.formatList($relation->getPivotColumns());
    }

    if (method_exists($relation, 'usesTimestamps')) {
        $parts[] = 'pivot_timestamps='.($relation->usesTimestamps() ? 'yes' : 'no');
    }

    if (method_exists($relation, 'getPivotClass')) {
        $parts[] = 'pivot_model='.$relation->getPivotClass();
    }

    return implode('; ', $parts);
}

/** @return list<class-string<Model>> */
function morphToTargetClasses(MorphTo $morphTo): array
{
    $targets = [];
    $parentClass = $morphTo->getParent()::class;

    foreach (RepresentativeModelManifest::classes() as $candidateClass) {
        $candidate = new $candidateClass;
        $reflection = new ReflectionClass($candidateClass);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== $candidateClass
                || $method->isStatic()
                || $method->getNumberOfRequiredParameters() > 0
                || relationReturnType($method) !== 'MorphMany') {
                continue;
            }

            $inverse = $method->invoke($candidate);

            if (! $inverse instanceof MorphMany
                || $inverse->getRelated()::class !== $parentClass
                || $inverse->getMorphType() !== $morphTo->getMorphType()
                || $inverse->getForeignKeyName() !== $morphTo->getForeignKeyName()) {
                continue;
            }

            $targets[] = $candidateClass;
        }
    }

    $targets = array_values(array_unique($targets));
    sort($targets);

    return $targets;
}

/**
 * @param  class-string<Model>  $modelClass
 * @return list<string>
 */
function polymorphicColumnsForModel(string $modelClass): array
{
    $columns = [];
    $model = new $modelClass;
    $reflection = new ReflectionClass($modelClass);

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->getDeclaringClass()->getName() !== $modelClass
            || $method->isStatic()
            || $method->getNumberOfRequiredParameters() > 0
            || relationReturnType($method) !== 'MorphTo') {
            continue;
        }

        $relation = $method->invoke($model);

        if (! $relation instanceof MorphTo) {
            continue;
        }

        $columns[] = $relation->getForeignKeyName();
        $columns[] = $relation->getMorphType();
    }

    $columns = array_values(array_unique($columns));
    sort($columns);

    return $columns;
}

/**
 * @param  array<string, array{foreign_keys: list<array<string, mixed>>}>  $tableSchemas
 * @param  array<string, class-string<Model>>  $modelClassesByTable
 * @return list<string>
 */
function userInverseExclusions(array $tableSchemas, array $modelClassesByTable): array
{
    $covered = [];
    $user = new User;
    $reflection = new ReflectionClass($user);

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->getDeclaringClass()->getName() !== $user::class
            || $method->isStatic()
            || $method->getNumberOfRequiredParameters() > 0
            || relationReturnType($method) === null) {
            continue;
        }

        $relation = $method->invoke($user);

        if (! $relation instanceof Relation) {
            continue;
        }

        if (method_exists($relation, 'getForeignKeyName')) {
            $covered[] = $relation->getRelated()->getTable().'.'.$relation->getForeignKeyName();
        }

        if (method_exists($relation, 'getTable') && method_exists($relation, 'getForeignPivotKeyName')) {
            $covered[] = $relation->getTable().'.'.$relation->getForeignPivotKeyName();
        }
    }

    $covered = array_values(array_unique($covered));
    $exclusions = [];

    foreach ($tableSchemas as $table => $schema) {
        if (! isset($modelClassesByTable[$table]) && $table === 'migrations') {
            continue;
        }

        foreach ($schema['foreign_keys'] as $foreignKey) {
            if (($foreignKey['foreign_table'] ?? null) !== 'users') {
                continue;
            }

            foreach (($foreignKey['columns'] ?? []) as $column) {
                $edge = $table.'.'.$column;

                if (! in_array($edge, $covered, true)) {
                    $exclusions[] = $edge;
                }
            }
        }
    }

    $exclusions = array_values(array_unique($exclusions));
    sort($exclusions);

    return $exclusions;
}

function relationReturnType(ReflectionMethod $method): ?string
{
    $returnType = $method->getReturnType();
    $types = $returnType instanceof ReflectionUnionType
        ? $returnType->getTypes()
        : ($returnType instanceof ReflectionNamedType ? [$returnType] : []);

    foreach ($types as $type) {
        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            continue;
        }

        $name = $type->getName();

        if (is_a($name, Relation::class, true)) {
            return class_basename($name);
        }
    }

    return null;
}

function methodSource(ReflectionMethod $method): string
{
    $path = $method->getFileName();

    if ($path === false) {
        return '';
    }

    $lines = file($path);

    if ($lines === false) {
        return '';
    }

    return implode('', array_slice(
        $lines,
        $method->getStartLine() - 1,
        $method->getEndLine() - $method->getStartLine() + 1,
    ));
}

/** @return array<string, string> */
function modelCasts(Model $model): array
{
    $casts = [];

    foreach ($model->getCasts() as $attribute => $cast) {
        $cast = (string) $cast;
        $base = explode(':', $cast, 2)[0];
        $enum = enum_exists($base);

        if (class_exists($base) || $enum) {
            $suffix = str_contains($cast, ':') ? ':'.explode(':', $cast, 2)[1] : '';
            $cast = class_basename($base).$suffix;
        }

        $casts[$attribute] = $cast.($enum ? ' [enum]' : '');
    }

    ksort($casts);

    return $casts;
}

function modelPrimaryKey(Model $model): string
{
    $incrementing = $model->getIncrementing() ? 'incrementing' : 'assigned';

    return $model->getKeyName().' ('.$model->getKeyType().", {$incrementing})";
}

/**
 * @param  list<array{name: string}>  $columns
 * @param  list<string>  $tableMigrations
 * @param  array<string, string>  $migrationSources
 * @return array<string, list<string>>
 */
function sourceColumnDeclarations(
    string $table,
    array $columns,
    array $tableMigrations,
    array $migrationSources,
): array {
    $sources = array_filter(
        $migrationSources,
        static fn (string $source, string $path): bool => in_array(
            basename($path),
            $tableMigrations,
            true,
        ),
        ARRAY_FILTER_USE_BOTH,
    );
    $declarations = [];
    $columnMethods = array_fill_keys([
        'bigIncrements', 'bigInteger', 'binary', 'boolean', 'char', 'date',
        'dateTime', 'dateTimeTz', 'decimal', 'double', 'enum', 'float',
        'foreignId', 'foreignIdFor', 'foreignUlid', 'foreignUuid', 'geometry',
        'geometryCollection', 'increments', 'integer', 'integerIncrements',
        'ipAddress', 'json', 'jsonb', 'longText', 'macAddress',
        'mediumIncrements', 'mediumInteger', 'mediumText', 'set',
        'smallIncrements', 'smallInteger', 'string', 'text', 'time', 'timeTz',
        'timestamp', 'timestampTz', 'tinyIncrements', 'tinyInteger', 'ulid',
        'unsignedBigInteger', 'unsignedInteger', 'unsignedMediumInteger',
        'unsignedSmallInteger', 'unsignedTinyInteger', 'uuid', 'vector', 'year',
    ], true);

    foreach ($columns as $column) {
        $name = $column['name'];
        $matchesForColumn = [];
        $quotedName = preg_quote($name, '/');

        foreach ($sources as $source) {
            foreach (tableSchemaCallbackBodies($source, $table) as $callback) {
                $variable = preg_quote($callback['variable'], '/');
                preg_match_all(
                    '/'.$variable.'\s*->([A-Za-z][A-Za-z0-9_]*)\(\s*([\'\"])'.$quotedName.'\2\s*((?:,[^;)]*)?)\)([^;]*);/s',
                    $callback['body'],
                    $matches,
                    PREG_SET_ORDER,
                );

                foreach ($matches as $match) {
                    if (! isset($columnMethods[$match[1]])) {
                        continue;
                    }

                    $arguments = trim(ltrim(trim($match[3]), ','));
                    $signature = $match[1].'('.$name.($arguments === '' ? '' : ', '.$arguments).')';
                    $modifiers = [];

                    foreach ([
                        'nullable', 'default', 'unique', 'index', 'constrained',
                        'storedAs', 'virtualAs', 'change', 'unsigned', 'charset',
                        'collation', 'comment', 'useCurrent', 'useCurrentOnUpdate',
                    ] as $modifier) {
                        if (str_contains($match[4], '->'.$modifier.'(')) {
                            $modifiers[] = $modifier;
                        }
                    }

                    $matchesForColumn[] = $signature
                        .($modifiers === [] ? '' : ' +'.implode('+', $modifiers));
                }

                $body = $callback['body'];
                $helper = match ($name) {
                    'id' => preg_match('/'.$variable.'\s*->(?:id|uuid|ulid)\(\s*\)/', $body) === 1
                        ? 'id-helper'
                        : null,
                    'created_at', 'updated_at' => preg_match('/'.$variable.'\s*->(?:timestamps|nullableTimestamps)\(\s*\)/', $body) === 1
                        ? 'timestamps-helper'
                        : null,
                    'deleted_at' => preg_match('/'.$variable.'\s*->softDeletes(?:Tz)?\(/', $body) === 1
                        ? 'softDeletes-helper'
                        : null,
                    'remember_token' => preg_match('/'.$variable.'\s*->rememberToken\(/', $body) === 1
                        ? 'rememberToken-helper'
                        : null,
                    default => null,
                };

                if ($helper !== null) {
                    $matchesForColumn[] = $helper;
                }

                if (str_ends_with($name, '_type') || str_ends_with($name, '_id')) {
                    $prefix = preg_quote(preg_replace('/_(?:type|id)$/', '', $name) ?? '', '/');

                    if ($prefix !== '' && preg_match(
                        '/'.$variable.'\s*->(?:nullable)?(?:uuid|ulid)?Morphs\(\s*([\'\"])'.$prefix.'\1/',
                        $body,
                    ) === 1) {
                        $matchesForColumn[] = 'morphs-helper('.$prefix.')';
                    }
                }
            }
        }

        $declarations[$name] = array_values(array_unique($matchesForColumn));
    }

    return $declarations;
}

/**
 * @return list<array{variable: string, body: string}>
 */
function tableSchemaCallbackBodies(string $source, string $table): array
{
    $upSource = migrationUpSource($source);
    $tokens = token_get_all("<?php\n".$upSource);
    $callbacks = [];
    $count = count($tokens);

    for ($index = 0; $index < $count; $index++) {
        if (! tokenIs($tokens[$index], T_STRING, 'Schema')) {
            continue;
        }

        $cursor = nextSignificantToken($tokens, $index + 1);

        if ($cursor === null || ! tokenIs($tokens[$cursor], T_DOUBLE_COLON)) {
            continue;
        }

        $cursor = nextSignificantToken($tokens, $cursor + 1);

        if ($cursor === null
            || ! is_array($tokens[$cursor])
            || $tokens[$cursor][0] !== T_STRING
            || ! in_array($tokens[$cursor][1], ['create', 'table'], true)) {
            continue;
        }

        $cursor = nextSignificantToken($tokens, $cursor + 1);

        if ($cursor === null || $tokens[$cursor] !== '(') {
            continue;
        }

        $cursor = nextSignificantToken($tokens, $cursor + 1);

        if ($cursor === null
            || ! is_array($tokens[$cursor])
            || $tokens[$cursor][0] !== T_CONSTANT_ENCAPSED_STRING
            || decodedPhpString($tokens[$cursor][1]) !== $table) {
            continue;
        }

        while (++$cursor < $count && ! tokenIs($tokens[$cursor], T_FUNCTION)) {
            if ($tokens[$cursor] === ';') {
                break;
            }
        }

        if ($cursor >= $count || ! tokenIs($tokens[$cursor], T_FUNCTION)) {
            continue;
        }

        $variable = null;
        $openingBrace = null;

        while (++$cursor < $count) {
            if ($variable === null && tokenIs($tokens[$cursor], T_VARIABLE)) {
                $variable = $tokens[$cursor][1];
            }

            if ($tokens[$cursor] === '{') {
                $openingBrace = $cursor;
                break;
            }
        }

        if ($variable === null || $openingBrace === null) {
            continue;
        }

        [$body, $closingBrace] = balancedTokenBody($tokens, $openingBrace);
        $callbacks[] = ['variable' => $variable, 'body' => $body];
        $index = $closingBrace;
    }

    return $callbacks;
}

function migrationUpSource(string $source): string
{
    $tokens = token_get_all($source);
    $count = count($tokens);

    for ($index = 0; $index < $count; $index++) {
        if (! tokenIs($tokens[$index], T_FUNCTION)) {
            continue;
        }

        $nameIndex = nextSignificantToken($tokens, $index + 1);

        if ($nameIndex === null || ! tokenIs($tokens[$nameIndex], T_STRING, 'up')) {
            continue;
        }

        while (++$nameIndex < $count && $tokens[$nameIndex] !== '{') {
            // Find the method body.
        }

        if ($nameIndex >= $count) {
            break;
        }

        [$body] = balancedTokenBody($tokens, $nameIndex);

        return $body;
    }

    // Unit fixtures and older migration fragments may contain direct Schema
    // calls without an anonymous migration wrapper.
    return $source;
}

/** @param list<array{0: int, 1: string, 2?: int}|string> $tokens */
function nextSignificantToken(array $tokens, int $start): ?int
{
    for ($index = $start, $count = count($tokens); $index < $count; $index++) {
        if (is_array($tokens[$index])
            && in_array($tokens[$index][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        return $index;
    }

    return null;
}

/**
 * @param  array{0: int, 1: string, 2?: int}|string  $token
 */
function tokenIs(array|string $token, int $type, ?string $text = null): bool
{
    return is_array($token)
        && $token[0] === $type
        && ($text === null || $token[1] === $text);
}

/**
 * @param  list<array{0: int, 1: string, 2?: int}|string>  $tokens
 * @return array{string, int}
 */
function balancedTokenBody(array $tokens, int $openingBrace): array
{
    $depth = 0;
    $interpolationDepth = 0;
    $body = '';

    for ($index = $openingBrace, $count = count($tokens); $index < $count; $index++) {
        $token = $tokens[$index];
        $text = is_array($token) ? $token[1] : $token;

        if (is_array($token)
            && in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true)) {
            $interpolationDepth++;
        } elseif ($token === '{') {
            $depth++;

            if ($depth === 1) {
                continue;
            }
        } elseif ($token === '}') {
            if ($interpolationDepth > 0) {
                $interpolationDepth--;
                $body .= $text;

                continue;
            }

            $depth--;

            if ($depth === 0) {
                return [$body, $index];
            }
        }

        $body .= $text;
    }

    throw new RuntimeException('Unbalanced migration callback body.');
}

/**
 * @param  array<string, class-string<Model>>  $modelClassesByTable
 * @param  array<string, array{columns: list<array{name: string}>, column_declarations: array<string, list<string>>}>  $tableSchemas
 */
function assertModelColumnSourceCoverage(
    array $modelClassesByTable,
    array $tableSchemas,
): void {
    $missing = [];

    foreach ($modelClassesByTable as $table => $modelClass) {
        $schema = $tableSchemas[$table] ?? null;

        if ($schema === null) {
            continue;
        }

        foreach ($schema['columns'] as $column) {
            if (($schema['column_declarations'][$column['name']] ?? []) === []) {
                $missing[] = $modelClass.':'.$table.'.'.$column['name'];
            }
        }
    }

    if ($missing !== []) {
        throw new RuntimeException(
            'Application model columns without a table-scoped source declaration: '
            .formatList($missing).'.',
        );
    }
}

function decodedPhpString(string $literal): string
{
    $quote = $literal[0] ?? '';
    $value = substr($literal, 1, -1);

    return $quote === "'"
        ? str_replace(['\\\\', "\\'"], ['\\', "'"], $value)
        : stripcslashes($value);
}

/**
 * @param  list<array{name: string, type_name: string, type: string, collation: ?string, nullable: bool, default: mixed, auto_increment: bool, comment: ?string, generation: array<string, mixed>|null}>  $columns
 * @param  array<string, list<string>>  $sourceDeclarations
 * @param  list<string>  $polymorphicColumns
 */
function columnContract(
    array $columns,
    array $sourceDeclarations,
    array $polymorphicColumns = [],
): string {
    $summaries = [];

    foreach ($columns as $column) {
        $name = $column['name'];
        $state = $column['nullable'] ? 'nullable' : 'required';
        $classifications = [];
        $source = $sourceDeclarations[$name] ?? [];
        $sourceText = implode(' + ', $source);

        if ($column['auto_increment']) {
            $classifications[] = 'auto-increment';
        }

        if ($column['generation'] !== null) {
            $classifications[] = 'generated='.formatSchemaValue($column['generation']);
        }

        if ($column['default'] !== null) {
            $classifications[] = 'default='.formatSchemaValue($column['default']);
        }

        if (is_string($column['collation']) && $column['collation'] !== '') {
            $classifications[] = 'collation='.$column['collation'];
        }

        if (is_string($column['comment']) && $column['comment'] !== '') {
            $classifications[] = 'comment='.$column['comment'];
        }

        if ($name === 'deleted_at' && str_contains($sourceText, 'softDeletes')) {
            $classifications[] = 'soft-delete';
        }

        if (preg_match('/\b(?:uuid|foreignUuid)\(/', $sourceText) === 1) {
            $classifications[] = 'UUID';
        }

        if (preg_match('/\b(?:ulid|foreignUlid)\(/', $sourceText) === 1) {
            $classifications[] = 'ULID';
        }

        if (str_contains($sourceText, 'morphs-helper') || in_array($name, $polymorphicColumns, true)) {
            $classifications[] = 'polymorphic';
        }

        $summaries[] = $name.':'.$column['type']
            .' ['.$state
            .($classifications === [] ? '' : '; '.implode('; ', $classifications))
            .']'
            .($source === [] ? '' : ' {source '.implode(' then ', $source).'}');
    }

    return implode('<br>', $summaries);
}

function formatSchemaValue(mixed $value): string
{
    if (is_array($value)) {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    if ($value === null) {
        return 'null';
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    return (string) $value;
}

/** @param list<array{name: string, columns: list<string>, unique: bool, primary: bool}> $indexes */
function indexContract(array $indexes): string
{
    $summaries = [];

    foreach ($indexes as $index) {
        $flags = [];

        if ($index['primary']) {
            $flags[] = 'primary';
        }

        if ($index['unique'] && ! $index['primary']) {
            $flags[] = 'unique';
        }

        $summaries[] = $index['name'].'('.implode(',', $index['columns']).')'
            .($flags === [] ? '' : '['.implode(',', $flags).']');
    }

    sort($summaries);

    return formatList($summaries);
}

/** @param list<array{name: string, columns: list<string>, unique: bool, primary: bool}> $indexes */
function uniqueContract(array $indexes): string
{
    $unique = [];

    foreach ($indexes as $index) {
        if ($index['unique'] && ! $index['primary']) {
            $unique[] = $index['name'].'('.implode(',', $index['columns']).')';
        }
    }

    sort($unique);

    return formatList($unique);
}

/**
 * @param  list<array{columns: list<string>, foreign_table: string, foreign_columns: list<string>, on_update: string, on_delete: string}>  $foreignKeys
 */
function foreignKeyContract(array $foreignKeys): string
{
    $summaries = [];

    foreach ($foreignKeys as $foreignKey) {
        $pairs = [];

        foreach ($foreignKey['columns'] as $offset => $column) {
            $pairs[] = $column.'→'.$foreignKey['foreign_table'].'.'.$foreignKey['foreign_columns'][$offset];
        }

        $summaries[] = implode('+', $pairs)
            .' [update '.$foreignKey['on_update'].', delete '.$foreignKey['on_delete'].']';
    }

    sort($summaries);

    return formatList($summaries);
}

/**
 * @param  class-string<Model>  $modelClass
 * @param  array<string, string>  $seederSources
 * @return list<string>
 */
function seedersForModel(string $modelClass, array $seederSources): array
{
    $model = class_basename($modelClass);
    $seeders = $modelClass === User::class
        ? []
        : ['RepresentativeDomainSeeder.php'];

    foreach ($seederSources as $path => $source) {
        if ($modelClass === User::class && basename($path) === 'RepresentativeDomainSeeder.php') {
            continue;
        }

        if (preg_match('/\b'.preg_quote($model, '/').'(?:::|;|\s)/', $source) === 1) {
            $seeders[] = basename($path);
        }
    }

    $seeders = array_values(array_unique($seeders));
    sort($seeders);

    return $seeders;
}

/**
 * @param  array<string, array{foreign_keys: list<array<string, mixed>>}>  $tableSchemas
 */
function seededOrphanCount(array $tableSchemas): int
{
    $total = 0;

    foreach ($tableSchemas as $table => $schema) {
        foreach ($schema['foreign_keys'] as $position => $foreignKey) {
            $childAlias = 'audit_child';
            $parentAlias = 'audit_parent_'.$position;
            $columns = $foreignKey['columns'];
            $foreignColumns = $foreignKey['foreign_columns'];
            $query = DB::table("{$table} as {$childAlias}")
                ->leftJoin(
                    $foreignKey['foreign_table']." as {$parentAlias}",
                    function (JoinClause $join) use (
                        $childAlias,
                        $columns,
                        $foreignColumns,
                        $parentAlias,
                    ): void {
                        foreach ($columns as $offset => $column) {
                            $join->on(
                                "{$parentAlias}.{$foreignColumns[$offset]}",
                                '=',
                                "{$childAlias}.{$column}",
                            );
                        }
                    },
                );

            foreach ($columns as $column) {
                $query->whereNotNull("{$childAlias}.{$column}");
            }

            $total += $query
                ->whereNull("{$parentAlias}.{$foreignColumns[0]}")
                ->count();
        }
    }

    return $total;
}

/** @param array<string, string|int> $values */
function formatMap(array $values): string
{
    if ($values === []) {
        return '—';
    }

    $items = [];

    foreach ($values as $key => $value) {
        $items[] = $key.':'.$value;
    }

    return implode(', ', $items);
}

/** @param list<string> $values */
function formatList(array $values): string
{
    return $values === [] ? '—' : implode(', ', $values);
}

function escapeCell(string $value): string
{
    return str_replace(['|', "\n", "\r"], ['\\|', ' ', ''], $value);
}

function escapeInline(string $value): string
{
    return str_replace(['`', "\n", "\r"], ['\\`', ' ', ''], $value);
}

function setAuditEnvironment(string $key, string $value): void
{
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

function assertAuditRuntimePath(string $runtimeDirectory, string $temporaryRoot): void
{
    if (dirname($runtimeDirectory) !== $temporaryRoot
        || ! str_starts_with(basename($runtimeDirectory), AUDIT_TEMP_PREFIX)) {
        throw new RuntimeException('Refusing audit runtime access outside its unique temporary directory.');
    }
}

function removeAuditRuntime(string $runtimeDirectory, string $temporaryRoot): void
{
    assertAuditRuntimePath($runtimeDirectory, $temporaryRoot);

    if (! is_dir($runtimeDirectory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($runtimeDirectory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($iterator as $item) {
        $path = $item->getPathname();

        if (! str_starts_with($path, $runtimeDirectory.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Refusing to remove an unvalidated audit runtime path.');
        }

        $removed = $item->isDir() ? rmdir($path) : unlink($path);

        if (! $removed) {
            fwrite(STDERR, "Unable to remove temporary audit path: {$path}\n");
        }
    }

    if (! rmdir($runtimeDirectory)) {
        fwrite(STDERR, "Unable to remove temporary audit directory: {$runtimeDirectory}\n");
    }
}
