<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sourcePath = $root.'/docs/requirements/forum-source-prompt.md';
$evidencePath = $root.'/docs/traceability/forum-requirement-evidence.json';
$jsonPath = $root.'/docs/requirements/forum-requirements.json';
$requirementsPath = $root.'/docs/requirements/forum-master-requirements.md';
$matrixPath = $root.'/docs/traceability/forum-requirements-matrix.md';
$phaseIndexPath = $root.'/docs/plans/forum-phase-requirement-index.md';
$checkOnly = in_array('--check', $argv, true);

if (! is_file($sourcePath)) {
    fwrite(STDERR, "Preserve the forum source prompt before generating requirements.\n");
    exit(1);
}

$source = file_get_contents($sourcePath);

if ($source === false) {
    fwrite(STDERR, "Unable to read {$sourcePath}.\n");
    exit(1);
}

preg_match('/<forum-source-primary>\n(.*)\n<\/forum-source-primary>/sU', $source, $primaryMatch);
preg_match('/<forum-source-extension>\n(.*)\n<\/forum-source-extension>/sU', $source, $extensionMatch);
preg_match('/Combined raw payload SHA-256: `([a-f0-9]{64})`/', $source, $checksumMatch);

if (! isset($primaryMatch[1], $extensionMatch[1], $checksumMatch[1])) {
    fwrite(STDERR, "The preserved forum prompt markers or checksum are invalid.\n");
    exit(1);
}

$parts = [
    'primary' => $primaryMatch[1],
    'extension' => $extensionMatch[1],
];
$payloadChecksum = hash('sha256', $parts['primary']."\n\n".$parts['extension']);

if (! hash_equals($checksumMatch[1], $payloadChecksum)) {
    fwrite(STDERR, "The preserved forum prompt checksum does not match its payload.\n");
    exit(1);
}

$requirements = [];
$prefixCounters = [];
$allowedStates = [
    'discovered',
    'analyzed',
    'planned',
    'approved-by-specification',
    'in-progress',
    'implemented',
    'migrated',
    'translated',
    'tested',
    'documented',
    'verified',
    'blocked',
    'intentionally-not-applicable',
];

foreach ($parts as $part => $contents) {
    $section = $part === 'primary'
        ? 'Original forum specification'
        : 'Additive master extension';
    $sectionPath = [$section];
    $lines = preg_split('/\R/u', $contents) ?: [];

    foreach ($lines as $index => $line) {
        $line = trim($line);

        if ($line === '' || $line === '---' || $line === '```') {
            continue;
        }

        if (preg_match('/^(#{1,6})\s+(.+)$/u', $line, $headingMatch) === 1) {
            $level = strlen($headingMatch[1]);
            $heading = trim($headingMatch[2]);
            $sectionPath = array_slice($sectionPath, 0, max(1, $level - 1));
            $sectionPath[] = $heading;
            $type = 'section';
            $verbatim = $heading;
        } else {
            $type = str_starts_with($line, '- ') || str_starts_with($line, '* ')
                ? 'bullet'
                : (preg_match('/^\d+\.\s+/u', $line) === 1 ? 'numbered-item' : 'statement');
            $verbatim = preg_replace('/^(?:[-*]|\d+\.)\s+/u', '', $line) ?? $line;
        }

        $domain = domainForRequirement($sectionPath, $verbatim);
        $prefix = identifierPrefix($domain);
        $prefixCounters[$prefix] = ($prefixCounters[$prefix] ?? 0) + 1;
        $requirementId = sprintf('%s.%04d', $prefix, $prefixCounters[$prefix]);
        $sourceSection = implode(' > ', $sectionPath);
        $phase = phaseForRequirement($domain, $sourceSection, $verbatim);

        $requirements[] = [
            'requirement_id' => $requirementId,
            'verbatim_source_requirement' => $verbatim,
            'normalized_implementation_requirement' => normalizeRequirement($type, $verbatim),
            'source_section' => $sourceSection,
            'source_part' => $part,
            'source_line' => $index + 1,
            'source_type' => $type,
            'domain' => $domain,
            'implementation_phase' => $phase,
            'priority' => priorityForRequirement($domain, $verbatim),
            'dependencies' => [],
            'current_implementation_status' => 'discovered',
            'discovered_existing_files' => [],
            'planned_files' => [],
            'database_impact' => impactFor($domain, 'database'),
            'backend_impact' => impactFor($domain, 'backend'),
            'livewire_impact' => impactFor($domain, 'livewire'),
            'interface_impact' => impactFor($domain, 'interface'),
            'authorization_impact' => impactFor($domain, 'authorization'),
            'privacy_impact' => impactFor($domain, 'privacy'),
            'security_impact' => impactFor($domain, 'security'),
            'moderation_impact' => impactFor($domain, 'moderation'),
            'translation_impact' => 'Use existing en, lt, and ru catalogues for platform-controlled text.',
            'cache_impact' => impactFor($domain, 'cache'),
            'migration_and_backfill_impact' => impactFor($domain, 'migration'),
            'seed_impact' => impactFor($domain, 'seed'),
            'factory_impact' => impactFor($domain, 'factory'),
            'test_identifiers' => [],
            'documentation_identifiers' => ['forum-doc-'.$requirementId],
            'implementation_status' => 'planned',
            'verification_status' => 'discovered',
            'evidence' => [],
            'unresolved_risks' => [],
            'final_result' => 'discovered',
        ];
    }
}

$requirements = applyEvidenceOverlay(
    $requirements,
    $evidencePath,
    $payloadChecksum,
    $allowedStates,
);
$domainCounts = [];

foreach ($requirements as $requirement) {
    $domainCounts[$requirement['domain']] = ($domainCounts[$requirement['domain']] ?? 0) + 1;
}

ksort($domainCounts);

$json = json_encode([
    'schema_version' => 1,
    'generated_at' => 'deterministic-from-source',
    'source_document' => 'docs/requirements/forum-source-prompt.md',
    'source_payload_sha256' => $payloadChecksum,
    'atomic_requirement_count' => count($requirements),
    'evidence_overlay' => 'docs/traceability/forum-requirement-evidence.json',
    'allowed_states' => $allowedStates,
    'requirements' => $requirements,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";

$requirementsDocument = "# Forum Master Requirements\n\n";
$requirementsDocument .= "This catalogue is generated from the immutable source prompt. It normalizes\n";
$requirementsDocument .= "the implementation contract without replacing or shortening source text.\n\n";
$requirementsDocument .= "- Source payload SHA-256: `{$payloadChecksum}`\n";
$requirementsDocument .= '- Atomic requirements: `'.count($requirements)."`\n";
$requirementsDocument .= "- Complete machine-readable catalogue: `docs/requirements/forum-requirements.json`\n";
$requirementsDocument .= "- Complete traceability index: `docs/traceability/forum-requirements-matrix.md`\n\n";
$requirementsDocument .= "- Evidence overlay: `docs/traceability/forum-requirement-evidence.json`\n\n";
$requirementsDocument .= "## Domain Counts\n\n| Domain | Atomic requirements |\n| --- | ---: |\n";

foreach ($domainCounts as $domain => $count) {
    $requirementsDocument .= "| {$domain} | {$count} |\n";
}

$requirementsDocument .= "\n## State Contract\n\n";
$requirementsDocument .= "Only the states declared in the JSON catalogue are valid. `blocked` is not\n";
$requirementsDocument .= "completion. `intentionally-not-applicable` requires evidence and a detailed\n";
$requirementsDocument .= "technical reason. `verified` requires file-level or test-level evidence.\n\n";
$requirementsDocument .= "## Requirement Record Contract\n\n";
$requirementsDocument .= "Every record preserves its verbatim source, normalized requirement, source\n";
$requirementsDocument .= "section and line, impacts, planned/evidence files, test and documentation\n";
$requirementsDocument .= "identifiers, status, risks, and final result. Status changes are performed by\n";
$requirementsDocument .= "the traceability updater and must never be inferred merely from file existence.\n";

$matrix = "# Forum Requirements Traceability Matrix\n\n";
$matrix .= "Generated from `docs/requirements/forum-requirements.json`.\n\n";
$matrix .= "- Source payload SHA-256: `{$payloadChecksum}`\n";
$matrix .= '- Atomic requirements: `'.count($requirements)."`\n\n";
$matrix .= "| Requirement ID | Source section | Verbatim atomic requirement | Domain | Phase | Priority | Implementation | Verification | Evidence |\n";
$matrix .= "| --- | --- | --- | --- | ---: | --- | --- | --- | --- |\n";

foreach ($requirements as $requirement) {
    $matrix .= '| '.escapeTable($requirement['requirement_id']);
    $matrix .= ' | '.escapeTable($requirement['source_section']);
    $matrix .= ' | '.escapeTable($requirement['verbatim_source_requirement']);
    $matrix .= ' | '.escapeTable($requirement['domain']);
    $matrix .= ' | '.escapeTable((string) $requirement['implementation_phase']);
    $matrix .= ' | '.escapeTable($requirement['priority']);
    $matrix .= ' | '.escapeTable($requirement['implementation_status']);
    $matrix .= ' | '.escapeTable($requirement['verification_status']);
    $matrix .= ' | '.escapeTable(
        $requirement['evidence'] === []
            ? 'None yet'
            : implode('; ', $requirement['evidence']),
    );
    $matrix .= " |\n";
}

$phaseIndex = "# Forum Phase Requirement Index\n\n";
$phaseIndex .= "This generated index assigns every atomic requirement to exactly one primary\n";
$phaseIndex .= "implementation phase. Cross-phase dependencies remain recorded in the master\n";
$phaseIndex .= "plan and requirement evidence; this primary assignment prevents orphaned work.\n\n";
$phaseIndex .= "- Source payload SHA-256: `{$payloadChecksum}`\n";
$phaseIndex .= '- Atomic requirements assigned: `'.count($requirements)."`\n\n";
$phaseIndex .= "| Requirement ID | Phase | Domain | Source section | Expected evidence |\n";
$phaseIndex .= "| --- | ---: | --- | --- | --- |\n";

foreach ($requirements as $requirement) {
    $phaseIndex .= '| '.escapeTable($requirement['requirement_id']);
    $phaseIndex .= ' | '.escapeTable((string) $requirement['implementation_phase']);
    $phaseIndex .= ' | '.escapeTable($requirement['domain']);
    $phaseIndex .= ' | '.escapeTable($requirement['source_section']);
    $phaseIndex .= ' | Code, migration, seed, test, or documentation evidence appropriate to the atomic requirement';
    $phaseIndex .= " |\n";
}

$outputs = [
    $jsonPath => $json,
    $requirementsPath => $requirementsDocument,
    $matrixPath => $matrix,
    $phaseIndexPath => $phaseIndex,
];

foreach ($outputs as $path => $contents) {
    if ($checkOnly) {
        if (! is_file($path) || file_get_contents($path) !== $contents) {
            fwrite(STDERR, "{$path} is stale.\n");
            exit(1);
        }

        continue;
    }

    $directory = dirname($path);

    if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
        fwrite(STDERR, "Unable to create {$directory}.\n");
        exit(1);
    }

    if (file_put_contents($path, $contents, LOCK_EX) === false) {
        fwrite(STDERR, "Unable to write {$path}.\n");
        exit(1);
    }
}

fwrite(STDOUT, sprintf(
    "%s %d atomic forum requirements from %s.\n",
    $checkOnly ? 'Verified' : 'Generated',
    count($requirements),
    $payloadChecksum,
));

/**
 * @param  list<array<string, mixed>>  $requirements
 * @param  list<string>  $allowedStates
 * @return list<array<string, mixed>>
 */
function applyEvidenceOverlay(
    array $requirements,
    string $evidencePath,
    string $payloadChecksum,
    array $allowedStates,
): array {
    if (! is_file($evidencePath)) {
        return $requirements;
    }

    $contents = file_get_contents($evidencePath);

    if ($contents === false) {
        throw new RuntimeException("Unable to read {$evidencePath}.");
    }

    $overlay = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($overlay)
        || ($overlay['source_payload_sha256'] ?? null) !== $payloadChecksum
        || ! is_array($overlay['updates'] ?? null)
    ) {
        throw new RuntimeException('The forum evidence overlay contract is invalid.');
    }

    $requirementsById = [];

    foreach ($requirements as $index => $requirement) {
        $requirementsById[$requirement['requirement_id']] = $index;
    }

    $mutableFields = [
        'dependencies',
        'current_implementation_status',
        'discovered_existing_files',
        'planned_files',
        'test_identifiers',
        'documentation_identifiers',
        'implementation_status',
        'verification_status',
        'evidence',
        'unresolved_risks',
        'final_result',
    ];
    $updatedIds = [];

    foreach ($overlay['updates'] as $update) {
        if (! is_array($update)
            || ! is_array($update['requirement_ids'] ?? null)
            || ! is_array($update['fields'] ?? null)
        ) {
            throw new RuntimeException('Every forum evidence update requires requirement_ids and fields.');
        }

        $unexpectedFields = array_diff(array_keys($update['fields']), $mutableFields);

        if ($unexpectedFields !== []) {
            throw new RuntimeException(
                'Forum evidence cannot replace immutable fields: '.implode(', ', $unexpectedFields),
            );
        }

        foreach ($update['requirement_ids'] as $requirementId) {
            if (! is_string($requirementId) || ! isset($requirementsById[$requirementId])) {
                throw new RuntimeException("Unknown forum evidence requirement id: {$requirementId}");
            }

            if (isset($updatedIds[$requirementId])) {
                throw new RuntimeException("Duplicate forum evidence requirement id: {$requirementId}");
            }

            $updatedIds[$requirementId] = true;
            $index = $requirementsById[$requirementId];
            $requirements[$index] = array_replace(
                $requirements[$index],
                $update['fields'],
            );

            foreach ([
                'current_implementation_status',
                'implementation_status',
                'verification_status',
                'final_result',
            ] as $stateField) {
                $state = $requirements[$index][$stateField];

                if (! is_string($state) || ! in_array($state, $allowedStates, true)) {
                    throw new RuntimeException(
                        "Invalid {$stateField} for {$requirementId}: ".(string) $state,
                    );
                }
            }

            if (
                $requirements[$index]['verification_status'] === 'verified'
                && $requirements[$index]['evidence'] === []
            ) {
                throw new RuntimeException(
                    "Verified requirement {$requirementId} requires concrete evidence.",
                );
            }
        }
    }

    return $requirements;
}

/** @param list<string> $sectionPath */
function domainForRequirement(array $sectionPath, string $verbatim): string
{
    $context = strtolower(implode(' ', $sectionPath).' '.$verbatim);

    return match (true) {
        str_contains($context, 'taxonomy'),
        str_contains($context, 'taxon'),
        str_contains($context, 'animal group'),
        str_contains($context, 'breed') => 'animal-taxonomy',
        str_contains($context, 'moderation'),
        str_contains($context, 'report'),
        str_contains($context, 'appeal'),
        str_contains($context, 'recusal') => 'moderation',
        str_contains($context, 'reputation'),
        str_contains($context, 'karma'),
        str_contains($context, 'trust level'),
        str_contains($context, 'badge'),
        str_contains($context, 'confirmation'),
        str_contains($context, 'consensus') => 'reputation-and-trust',
        str_contains($context, 'category'),
        str_contains($context, 'subcategory') => 'forum-category',
        str_contains($context, 'translation'),
        str_contains($context, 'multilingual'),
        str_contains($context, 'locale') => 'localization',
        str_contains($context, 'seed'),
        str_contains($context, 'factory') => 'seeding',
        str_contains($context, 'test'),
        str_contains($context, 'verification'),
        str_contains($context, 'completeness gate') => 'testing-and-traceability',
        str_contains($context, 'security'),
        str_contains($context, 'privacy'),
        str_contains($context, 'authorization'),
        str_contains($context, 'permission') => 'security-and-privacy',
        str_contains($context, 'search'),
        str_contains($context, 'feed'),
        str_contains($context, 'discovery') => 'search-and-discovery',
        str_contains($context, 'livewire'),
        str_contains($context, 'interface'),
        str_contains($context, 'accessibility'),
        str_contains($context, 'blade') => 'interface',
        str_contains($context, 'migration'),
        str_contains($context, 'database'),
        str_contains($context, 'schema'),
        str_contains($context, 'concurrency') => 'persistence',
        str_contains($context, 'plan'),
        str_contains($context, 'documentation'),
        str_contains($context, 'audit'),
        str_contains($context, 'final report') => 'planning-and-documentation',
        default => 'forum-feature',
    };
}

function identifierPrefix(string $domain): string
{
    return match ($domain) {
        'animal-taxonomy' => 'animal.taxonomy',
        'moderation' => 'forum.moderation',
        'reputation-and-trust' => 'forum.reputation',
        'forum-category' => 'forum.category',
        'localization' => 'forum.translation',
        'seeding' => 'forum.seed',
        'testing-and-traceability' => 'forum.test',
        'security-and-privacy' => 'forum.security',
        'search-and-discovery' => 'forum.search',
        'interface' => 'forum.interface',
        'persistence' => 'forum.data',
        'planning-and-documentation' => 'forum.plan',
        default => 'forum.feature',
    };
}

function phaseForRequirement(string $domain, string $sourceSection, string $verbatim): int
{
    $context = strtolower($sourceSection.' '.$verbatim);

    if (str_contains($context, 'phase 0') || str_contains($context, 'source preservation')) {
        return 0;
    }

    if (
        str_contains($context, 'phase 1')
        || str_contains($context, 'repository audit')
        || str_contains($context, 'discovery')
    ) {
        return 1;
    }

    if (
        str_contains($context, 'phase 2')
        || str_contains($context, 'domain design')
        || str_contains($context, 'architecture decision')
    ) {
        return 2;
    }

    if (
        str_contains($context, 'phase 3')
        || ($domain === 'persistence' && ! str_contains($context, 'migration and backfill'))
    ) {
        return 3;
    }

    if (
        str_contains($context, 'phase 4')
        || $domain === 'forum-category'
    ) {
        return 4;
    }

    if (
        str_contains($context, 'phase 5')
        || $domain === 'animal-taxonomy'
    ) {
        return 5;
    }

    if (
        str_contains($context, 'phase 6')
        || $domain === 'reputation-and-trust'
    ) {
        return 6;
    }

    if (
        str_contains($context, 'phase 7')
        || $domain === 'moderation'
    ) {
        return 7;
    }

    if (
        str_contains($context, 'phase 8')
        || $domain === 'forum-feature'
    ) {
        return 8;
    }

    if (
        str_contains($context, 'phase 9')
        || $domain === 'search-and-discovery'
    ) {
        return 9;
    }

    if (
        str_contains($context, 'phase 10')
        || $domain === 'interface'
    ) {
        return 10;
    }

    if (
        str_contains($context, 'phase 11')
        || str_contains($context, 'migration and backfill')
        || str_contains($context, 'legacy')
    ) {
        return 11;
    }

    if (
        str_contains($context, 'phase 12')
        || $domain === 'seeding'
    ) {
        return 12;
    }

    if (
        str_contains($context, 'phase 13')
        || $domain === 'testing-and-traceability'
    ) {
        return 13;
    }

    return 14;
}

function normalizeRequirement(string $type, string $verbatim): string
{
    return match ($type) {
        'section' => "Preserve and implement the source section '{$verbatim}' as an independently traceable scope.",
        'bullet', 'numbered-item' => "Implement and independently verify: {$verbatim}",
        default => $verbatim,
    };
}

function priorityForRequirement(string $domain, string $verbatim): string
{
    $value = strtolower($verbatim);

    if (
        in_array($domain, ['security-and-privacy', 'moderation'], true)
        || str_contains($value, 'must not')
        || str_contains($value, 'never')
        || str_contains($value, 'critical')
        || str_contains($value, 'private')
    ) {
        return 'critical';
    }

    return in_array($domain, ['persistence', 'animal-taxonomy', 'testing-and-traceability'], true)
        ? 'high'
        : 'standard';
}

function impactFor(string $domain, string $impact): string
{
    $relevant = [
        'database' => ['animal-taxonomy', 'moderation', 'reputation-and-trust', 'forum-category', 'persistence', 'forum-feature'],
        'backend' => ['animal-taxonomy', 'moderation', 'reputation-and-trust', 'forum-category', 'search-and-discovery', 'persistence', 'forum-feature'],
        'livewire' => ['interface', 'forum-category', 'search-and-discovery', 'moderation', 'animal-taxonomy', 'forum-feature'],
        'interface' => ['interface', 'forum-category', 'search-and-discovery', 'moderation', 'animal-taxonomy', 'forum-feature'],
        'authorization' => ['security-and-privacy', 'moderation', 'reputation-and-trust', 'animal-taxonomy', 'forum-feature'],
        'privacy' => ['security-and-privacy', 'moderation', 'search-and-discovery', 'forum-feature'],
        'security' => ['security-and-privacy', 'moderation', 'reputation-and-trust', 'interface', 'forum-feature'],
        'moderation' => ['moderation', 'reputation-and-trust', 'forum-category', 'forum-feature'],
        'cache' => ['animal-taxonomy', 'forum-category', 'search-and-discovery', 'reputation-and-trust'],
        'migration' => ['animal-taxonomy', 'moderation', 'reputation-and-trust', 'forum-category', 'persistence', 'forum-feature'],
        'seed' => ['animal-taxonomy', 'moderation', 'reputation-and-trust', 'forum-category', 'seeding'],
        'factory' => ['animal-taxonomy', 'moderation', 'reputation-and-trust', 'forum-category', 'persistence', 'forum-feature'],
    ];

    return in_array($domain, $relevant[$impact] ?? [], true)
        ? 'Requires phase-specific analysis and evidence.'
        : 'No direct impact identified during atomic extraction.';
}

function escapeTable(string $value): string
{
    return str_replace(['|', "\n"], ['\\|', ' '], $value);
}
