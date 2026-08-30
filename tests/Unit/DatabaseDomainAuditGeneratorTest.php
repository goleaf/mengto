<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RepresentativeModelManifest;
use Symfony\Component\Process\Process;

function databaseDomainAuditRoot(): string
{
    return dirname(__DIR__, 2);
}

require_once databaseDomainAuditRoot().'/scripts/generate-database-domain-audit.php';

/** @param array<string, mixed> $evidence */
function runDatabaseDomainAuditIntegrityProbe(array $evidence): Process
{
    $generator = databaseDomainAuditRoot().'/scripts/generate-database-domain-audit.php';
    $payload = base64_encode((string) json_encode($evidence, JSON_THROW_ON_ERROR));
    $code = 'require '.var_export($generator, true).'; '
        .'$evidence = json_decode(base64_decode('.var_export($payload, true).'), true, flags: JSON_THROW_ON_ERROR); '
        .'assertAuditIntegrity('
        .'$evidence["migration_files"], '
        .'$evidence["migration_inventory"], '
        .'$evidence["known_tables"], '
        .'$evidence["missing_factories"], '
        .'$evidence["underfilled_models"], '
        .'$evidence["orphan_count"], '
        .'$evidence["target_actor_key"]'
        .');';
    $process = new Process([PHP_BINARY, '-r', $code], databaseDomainAuditRoot());
    $process->setTimeout(30);
    $process->run();

    return $process;
}

/** @return array<string, mixed> */
function validDatabaseDomainAuditEvidence(): array
{
    return [
        'migration_files' => ['/tmp/0001_create_users_table.php'],
        'migration_inventory' => [
            '0001_create_users_table.php' => ['users'],
        ],
        'known_tables' => ['users'],
        'missing_factories' => [],
        'underfilled_models' => [],
        'orphan_count' => 0,
        'target_actor_key' => 'mia-carter',
    ];
}

dataset('invalid database audit integrity evidence', [
    'missing factory' => [
        ['missing_factories' => ['App\\Models\\PetProfile']],
        'Missing model factories',
    ],
    'model below target' => [
        ['underfilled_models' => ['App\\Models\\PetProfile' => 9]],
        'Models below the representative seed target',
    ],
    'seeded foreign-key orphan' => [
        ['orphan_count' => 1],
        'Seeded foreign-key orphans',
    ],
    'missing deterministic user' => [
        ['target_actor_key' => null],
        'user@example.com must exist with actor key mia-carter',
    ],
    'wrong deterministic actor' => [
        ['target_actor_key' => 'wrong-actor'],
        'user@example.com must exist with actor key mia-carter',
    ],
    'migration absent from inventory' => [
        ['migration_inventory' => []],
        'Migration inventory is incomplete',
    ],
    'migration has no mapped table' => [
        ['migration_inventory' => ['0001_create_users_table.php' => []]],
        'Migration inventory contains unmapped files',
    ],
    'migration maps a nonexistent table' => [
        ['migration_inventory' => ['0001_create_users_table.php' => ['missing_table']]],
        'Migration inventory references unknown tables',
    ],
    'runtime table has no migration mapping' => [
        ['known_tables' => ['users', 'runtime_only_table']],
        'Runtime tables without a migration mapping',
    ],
]);

test('the integrity gate accepts complete audit evidence', function () {
    $process = runDatabaseDomainAuditIntegrityProbe(validDatabaseDomainAuditEvidence());

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());
});

test('data only migrations map the table of an explicitly queried first party model', function () {
    $inventory = migrationInventory([
        '/tmp/0001_seed_identity_lock.php' => <<<'PHP'
<?php

use App\Models\PlaceSubmissionIdentityLock;

return new class {
    public function up(): void
    {
        PlaceSubmissionIdentityLock::query()->firstOrCreate(['identity_hash' => 'fixed']);
    }
};
PHP,
    ]);

    expect($inventory)->toBe([
        '0001_seed_identity_lock.php' => ['place_submission_identity_locks'],
    ]);
});

test('the integrity gate exits nonzero for invalid audit evidence', function (
    array $overrides,
    string $expectedError,
) {
    $process = runDatabaseDomainAuditIntegrityProbe([
        ...validDatabaseDomainAuditEvidence(),
        ...$overrides,
    ]);

    expect($process->isSuccessful())->toBeFalse()
        ->and($process->getErrorOutput())->toContain($expectedError);
})->with('invalid database audit integrity evidence');

test('the generated audit inventories every migration and exact seeded model count', function () {
    $root = databaseDomainAuditRoot();
    $document = file_get_contents($root.'/docs/audits/database-domain-audit.md');
    $migrationFiles = glob($root.'/database/migrations/*.php') ?: [];

    expect($document)->toBeString()
        ->and($document)->not->toContain('≥10 verified');

    foreach ($migrationFiles as $migrationFile) {
        expect($document)->toContain('| `'.basename($migrationFile).'` |');
    }

    preg_match_all(
        '/^\| [A-Za-z][^|]+ \| .* \| ([0-9]+) observed \(target ≥10: met\) \|$/m',
        $document,
        $seededRows,
    );

    expect($seededRows[1])->toHaveCount(count(RepresentativeModelManifest::classes()));
});

test('the committed audit passes the isolated regeneration check', function () {
    $process = new Process([
        PHP_BINARY,
        databaseDomainAuditRoot().'/scripts/generate-database-domain-audit.php',
        '--check',
    ], databaseDomainAuditRoot());
    $process->setTimeout(180);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput().$process->getOutput())
        ->and($process->getOutput())->toContain('docs/audits/database-domain-audit.md is current.');
});

test('source declarations are scoped to the target table and column methods', function () {
    $migration = <<<'PHP'
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class {
    public function up(): void
    {
        Schema::table('search_contact_relays', function (Blueprint $table): void {
            $table->string('status', 30)->default('pending');
            $table->where('status', 'pending');
            $table->dropColumn('status');
        });

        $article = (object) ['id' => 1];
        $key = "guide-{$article->id}";

        Schema::table('search_cases', function (Blueprint $table): void {
            $table->string('status', 40)->index();
            $table->after('status', function (Blueprint $table): void {
                $table->string('risk_level', 24)->default('normal');
            });
        });
    }
};
PHP;

    $declarations = sourceColumnDeclarations(
        'search_cases',
        [['name' => 'status']],
        ['0001_test.php'],
        ['/tmp/0001_test.php' => $migration],
    );

    expect($declarations['status'])->toBe(['string(status, 40) +index']);
});

test('model column source coverage fails closed when a declaration is absent', function () {
    expect(fn () => assertModelColumnSourceCoverage(
        ['users' => User::class],
        [
            'users' => [
                'columns' => [['name' => 'email']],
                'column_declarations' => ['email' => []],
            ],
        ],
    ))->toThrow(
        RuntimeException::class,
        'Application model columns without a table-scoped source declaration',
    );
});

test('morph to contracts identify dynamic supported targets and manual morph columns', function () {
    $root = databaseDomainAuditRoot();
    $code = 'require '.var_export($root.'/vendor/autoload.php', true).'; '
        .'require '.var_export($root.'/scripts/generate-database-domain-audit.php', true).'; '
        .'$app = require '.var_export($root.'/bootstrap/app.php', true).'; '
        .'$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); '
        .'$report = new App\\Models\\ForumReport; '
        .'echo json_encode(['
        .'"contract" => relationshipContract($report->subject()), '
        .'"columns" => polymorphicColumnsForModel(App\\Models\\ForumReport::class),'
        .'], JSON_THROW_ON_ERROR);';
    $process = new Process([PHP_BINARY, '-r', $code], $root);
    $process->setTimeout(30);
    $process->run();
    $payload = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    $contract = $payload['contract'];

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
        ->and($contract)
        ->toContain('MorphTo; related=dynamic; related_table=dynamic')
        ->toContain('foreign_key=subject_id')
        ->toContain('morph_type=subject_type')
        ->toContain('morph_targets=')
        ->not->toContain('related=App\\Models\\ForumReport')
        ->and($payload['columns'])->toBe(['subject_id', 'subject_type']);
});

test('user seeder attribution excludes the representative top up that skips users', function () {
    $seeders = seedersForModel(User::class, [
        '/tmp/DatabaseSeeder.php' => 'use App\\Models\\User; User::query();',
        '/tmp/RepresentativeDomainSeeder.php' => 'use App\\Models\\User; User::class;',
    ]);

    expect($seeders)->toBe(['DatabaseSeeder.php']);
});
