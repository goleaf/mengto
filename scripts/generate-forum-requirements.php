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
preg_match('/<pet-profile-source-revision>\n(.*)\n<\/pet-profile-source-revision>/sU', $source, $petProfileMatch);
preg_match('/<social-relationships-source-revision>\n(.*)\n<\/social-relationships-source-revision>/sU', $source, $socialRelationshipsMatch);
preg_match('/<content-feed-source-revision>\n(.*)\n<\/content-feed-source-revision>/sU', $source, $contentFeedMatch);
preg_match('/<communication-source-revision>\n(.*)\n<\/communication-source-revision>/sU', $source, $communicationMatch);
preg_match('/<community-source-revision>\n(.*)\n<\/community-source-revision>/sU', $source, $communityMatch);
preg_match('/<medical-record-source-revision>\n(.*)\n<\/medical-record-source-revision>/sU', $source, $medicalRecordMatch);
preg_match('/<portal-architecture-source-revision>\n(.*)\n<\/portal-architecture-source-revision>/sU', $source, $portalArchitectureMatch);
preg_match('/<event-lifecycle-source-revision>\n(.*)\n<\/event-lifecycle-source-revision>/sU', $source, $eventLifecycleMatch);
preg_match('/Combined raw payload SHA-256: `([a-f0-9]{64})`/', $source, $checksumMatch);
preg_match('/Revision raw payload SHA-256: `([a-f0-9]{64})`/', $source, $petProfileChecksumMatch);
preg_match('/Master raw payload SHA-256: `([a-f0-9]{64})`/', $source, $masterChecksumMatch);
preg_match('/Social revision raw payload SHA-256: `([a-f0-9]{64})`/', $source, $socialRelationshipsChecksumMatch);
preg_match('/Current master raw payload SHA-256: `([a-f0-9]{64})`/', $source, $currentMasterChecksumMatch);
preg_match('/Content revision raw payload SHA-256: `([a-f0-9]{64})`/', $source, $contentFeedChecksumMatch);
preg_match('/Latest master raw payload SHA-256: `([a-f0-9]{64})`/', $source, $latestMasterChecksumMatch);
preg_match('/Communication revision raw payload SHA-256: `([a-f0-9]{64})`/', $source, $communicationChecksumMatch);
preg_match('/Complete master raw payload SHA-256: `([a-f0-9]{64})`/', $source, $completeMasterChecksumMatch);
preg_match('/Community revision raw payload SHA-256: `([a-f0-9]{64})`/', $source, $communityChecksumMatch);
preg_match('/Expanded master raw payload SHA-256: `([a-f0-9]{64})`/', $source, $expandedMasterChecksumMatch);
preg_match('/Medical revision raw payload SHA-256: `([a-f0-9]{64})`/', $source, $medicalRecordChecksumMatch);
preg_match('/Medical master raw payload SHA-256: `([a-f0-9]{64})`/', $source, $medicalMasterChecksumMatch);
preg_match('/Portal revision raw payload SHA-256: `([a-f0-9]{64})`/', $source, $portalArchitectureChecksumMatch);
preg_match('/Portal master raw payload SHA-256: `([a-f0-9]{64})`/', $source, $portalMasterChecksumMatch);
preg_match('/Event revision raw payload SHA-256: `([a-f0-9]{64})`/', $source, $eventLifecycleChecksumMatch);
preg_match('/Event master raw payload SHA-256: `([a-f0-9]{64})`/', $source, $eventMasterChecksumMatch);

if (! isset(
    $primaryMatch[1],
    $extensionMatch[1],
    $petProfileMatch[1],
    $socialRelationshipsMatch[1],
    $contentFeedMatch[1],
    $communicationMatch[1],
    $communityMatch[1],
    $medicalRecordMatch[1],
    $portalArchitectureMatch[1],
    $eventLifecycleMatch[1],
    $checksumMatch[1],
    $petProfileChecksumMatch[1],
    $masterChecksumMatch[1],
    $socialRelationshipsChecksumMatch[1],
    $currentMasterChecksumMatch[1],
    $contentFeedChecksumMatch[1],
    $latestMasterChecksumMatch[1],
    $communicationChecksumMatch[1],
    $completeMasterChecksumMatch[1],
    $communityChecksumMatch[1],
    $expandedMasterChecksumMatch[1],
    $medicalRecordChecksumMatch[1],
    $medicalMasterChecksumMatch[1],
    $portalArchitectureChecksumMatch[1],
    $portalMasterChecksumMatch[1],
    $eventLifecycleChecksumMatch[1],
    $eventMasterChecksumMatch[1],
)) {
    fwrite(STDERR, "The preserved forum prompt markers or checksum are invalid.\n");
    exit(1);
}

$parts = [
    'primary' => $primaryMatch[1],
    'extension' => $extensionMatch[1],
    'pet-profile-revision' => $petProfileMatch[1],
    'social-relationships-revision' => $socialRelationshipsMatch[1],
    'content-feed-revision' => $contentFeedMatch[1],
    'communication-revision' => $communicationMatch[1],
    'community-revision' => $communityMatch[1],
    'medical-record-revision' => $medicalRecordMatch[1],
    'portal-architecture-revision' => $portalArchitectureMatch[1],
    'event-lifecycle-revision' => $eventLifecycleMatch[1],
];
$forumPayload = $parts['primary']."\n\n".$parts['extension'];
$forumPayloadChecksum = hash('sha256', $forumPayload);
$petProfilePayloadChecksum = hash('sha256', $parts['pet-profile-revision']);
$petProfileMasterPayload = $forumPayload."\n\n".$parts['pet-profile-revision'];
$petProfileMasterChecksum = hash('sha256', $petProfileMasterPayload);
$socialRelationshipsPayloadChecksum = hash('sha256', $parts['social-relationships-revision']);
$socialRelationshipsMasterPayload = $petProfileMasterPayload."\n\n".$parts['social-relationships-revision'];
$socialRelationshipsMasterChecksum = hash('sha256', $socialRelationshipsMasterPayload);
$contentFeedPayloadChecksum = hash('sha256', $parts['content-feed-revision']);
$contentFeedMasterPayload = $socialRelationshipsMasterPayload."\n\n".$parts['content-feed-revision'];
$contentFeedMasterChecksum = hash('sha256', $contentFeedMasterPayload);
$communicationPayloadChecksum = hash('sha256', $parts['communication-revision']);
$communicationMasterPayload = $contentFeedMasterPayload."\n\n".$parts['communication-revision'];
$communicationMasterChecksum = hash('sha256', $communicationMasterPayload);
$communityPayloadChecksum = hash('sha256', $parts['community-revision']);
$communityMasterPayload = $communicationMasterPayload."\n\n".$parts['community-revision'];
$communityMasterChecksum = hash('sha256', $communityMasterPayload);
$medicalRecordPayloadChecksum = hash('sha256', $parts['medical-record-revision']);
$medicalRecordMasterPayload = $communityMasterPayload."\n\n".$parts['medical-record-revision'];
$medicalRecordMasterChecksum = hash('sha256', $medicalRecordMasterPayload);
$portalArchitecturePayloadChecksum = hash('sha256', $parts['portal-architecture-revision']);
$portalArchitectureMasterPayload = $medicalRecordMasterPayload."\n\n".$parts['portal-architecture-revision'];
$portalArchitectureMasterChecksum = hash('sha256', $portalArchitectureMasterPayload);
$eventLifecyclePayloadChecksum = hash('sha256', $parts['event-lifecycle-revision']);
$payloadChecksum = hash('sha256', $portalArchitectureMasterPayload."\n\n".$parts['event-lifecycle-revision']);

if (! hash_equals($checksumMatch[1], $forumPayloadChecksum)
    || ! hash_equals($petProfileChecksumMatch[1], $petProfilePayloadChecksum)
    || ! hash_equals($masterChecksumMatch[1], $petProfileMasterChecksum)
    || ! hash_equals($socialRelationshipsChecksumMatch[1], $socialRelationshipsPayloadChecksum)
    || ! hash_equals($currentMasterChecksumMatch[1], $socialRelationshipsMasterChecksum)
    || ! hash_equals($contentFeedChecksumMatch[1], $contentFeedPayloadChecksum)
    || ! hash_equals($latestMasterChecksumMatch[1], $contentFeedMasterChecksum)
    || ! hash_equals($communicationChecksumMatch[1], $communicationPayloadChecksum)
    || ! hash_equals($completeMasterChecksumMatch[1], $communicationMasterChecksum)
    || ! hash_equals($communityChecksumMatch[1], $communityPayloadChecksum)
    || ! hash_equals($expandedMasterChecksumMatch[1], $communityMasterChecksum)
    || ! hash_equals($medicalRecordChecksumMatch[1], $medicalRecordPayloadChecksum)
    || ! hash_equals($medicalMasterChecksumMatch[1], $medicalRecordMasterChecksum)
    || ! hash_equals($portalArchitectureChecksumMatch[1], $portalArchitecturePayloadChecksum)
    || ! hash_equals($portalMasterChecksumMatch[1], $portalArchitectureMasterChecksum)
    || ! hash_equals($eventLifecycleChecksumMatch[1], $eventLifecyclePayloadChecksum)
    || ! hash_equals($eventMasterChecksumMatch[1], $payloadChecksum)
) {
    fwrite(STDERR, "A preserved source-prompt checksum does not match its payload.\n");
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
    $section = match ($part) {
        'primary' => 'Original forum specification',
        'extension' => 'Additive master extension',
        'pet-profile-revision' => 'Pet profile and full lifecycle revision',
        'social-relationships-revision' => 'Social relationships and safe introductions revision',
        'content-feed-revision' => 'Content feed and distribution revision',
        'communication-revision' => 'Safe communication revision',
        'community-revision' => 'Communities and full lifecycle revision',
        'medical-record-revision' => 'Medical record and full clinical history revision',
        'portal-architecture-revision' => 'Canonical portal architecture revision',
        default => 'Events and complete lifecycle revision',
    };
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

        $domain = domainForRequirement($sectionPath, $verbatim, $part);
        $prefix = identifierPrefix($domain);
        $prefixCounters[$prefix] = ($prefixCounters[$prefix] ?? 0) + 1;
        $requirementId = sprintf('%s.%04d', $prefix, $prefixCounters[$prefix]);
        $sourceSection = implode(' > ', $sectionPath);
        $phase = phaseForRequirement($domain, $sourceSection, $verbatim, $part);

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
    'source_parts' => array_keys($parts),
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
function domainForRequirement(array $sectionPath, string $verbatim, string $sourcePart): string
{
    if ($sourcePart === 'pet-profile-revision') {
        return petDomainForRequirement($sectionPath, $verbatim);
    }

    if ($sourcePart === 'social-relationships-revision') {
        return socialDomainForRequirement($sectionPath, $verbatim);
    }

    if ($sourcePart === 'content-feed-revision') {
        return contentDomainForRequirement($sectionPath, $verbatim);
    }

    if ($sourcePart === 'communication-revision') {
        return communicationDomainForRequirement($sectionPath, $verbatim);
    }

    if ($sourcePart === 'community-revision') {
        return communityDomainForRequirement($sectionPath, $verbatim);
    }

    if ($sourcePart === 'medical-record-revision') {
        return medicalDomainForRequirement($sectionPath, $verbatim);
    }

    if ($sourcePart === 'portal-architecture-revision') {
        return portalDomainForRequirement($sectionPath, $verbatim);
    }

    if ($sourcePart === 'event-lifecycle-revision') {
        return eventDomainForRequirement($sectionPath, $verbatim);
    }

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

/** @param list<string> $sectionPath */
function portalDomainForRequirement(array $sectionPath, string $verbatim): string
{
    $context = strtolower(implode(' ', $sectionPath).' '.$verbatim);

    return match (true) {
        str_contains($context, 'route'), str_contains($context, 'page map'),
        str_contains($context, 'module registry') => 'portal-route-and-page-registry',
        str_contains($context, 'navigation'), str_contains($context, 'breadcrumb'),
        str_contains($context, 'switcher') => 'portal-navigation',
        str_contains($context, 'dashboard'), str_contains($context, 'workspace'),
        str_contains($context, 'widget') => 'portal-dashboard-workspace',
        str_contains($context, 'search'), str_contains($context, 'discovery'),
        str_contains($context, 'feed') => 'portal-search-discovery',
        str_contains($context, 'notification'), str_contains($context, 'calendar'),
        str_contains($context, 'messaging') => 'portal-notification-calendar',
        str_contains($context, 'authorization'), str_contains($context, 'permission'),
        str_contains($context, 'privacy'), str_contains($context, 'security') => 'portal-security-privacy',
        str_contains($context, 'ui'), str_contains($context, 'interface'),
        str_contains($context, 'accessibility'), str_contains($context, 'localization'),
        str_contains($context, 'responsive') => 'portal-interface-quality',
        str_contains($context, 'workflow'), str_contains($context, 'integration') => 'portal-workflow-integration',
        str_contains($context, 'test'), str_contains($context, 'factory'),
        str_contains($context, 'seed'), str_contains($context, 'verification') => 'portal-quality-release',
        str_contains($context, 'document'), str_contains($context, 'matrix'),
        str_contains($context, 'report') => 'portal-documentation',
        default => 'portal-foundation',
    };
}

/** @param list<string> $sectionPath */
function eventDomainForRequirement(array $sectionPath, string $verbatim): string
{
    $context = strtolower(implode(' ', $sectionPath).' '.$verbatim);

    return match (true) {
        str_contains($context, 'event-type-') => 'event-type',
        str_contains($context, 'event-life-') => 'event-lifecycle',
        str_contains($context, 'event-org-') => 'event-organization',
        str_contains($context, 'event-create-') => 'event-creation',
        str_contains($context, 'event-time-') => 'event-schedule-recurrence',
        str_contains($context, 'venue-') => 'event-venue',
        str_contains($context, 'eligibility-') => 'event-eligibility',
        str_contains($context, 'registration-') => 'event-registration',
        str_contains($context, 'capacity-') => 'event-capacity-waitlist',
        str_contains($context, 'ticket-') => 'event-ticket-payment',
        str_contains($context, 'walk-') => 'event-group-walk',
        str_contains($context, 'training-') => 'event-training',
        str_contains($context, 'exhibition-') => 'event-exhibition',
        str_contains($context, 'competition-') => 'event-competition',
        str_contains($context, 'conference-') => 'event-conference',
        str_contains($context, 'vendor-'), str_contains($context, 'sponsor-') => 'event-vendor-sponsor',
        str_contains($context, 'volunteer-') => 'event-volunteer',
        str_contains($context, 'communication-') => 'event-communication',
        str_contains($context, 'checkin-') => 'event-check-in',
        str_contains($context, 'weather-'), str_contains($context, 'cancel-') => 'event-weather-cancellation',
        str_contains($context, 'safety-') => 'event-safety-incident',
        str_contains($context, 'media-'), str_contains($context, 'privacy-') => 'event-media-privacy',
        str_contains($context, 'feedback-') => 'event-feedback-archive',
        str_contains($context, 'event-notify-') => 'event-notification-calendar',
        str_contains($context, 'event-discovery-'), str_contains($context, 'event-feed-'),
        str_contains($context, 'event-seo-') => 'event-discovery-seo',
        str_contains($context, 'integration-') => 'event-integration',
        str_contains($context, 'event-data-') => 'event-data',
        str_contains($context, 'event-auth-') => 'event-authorization',
        str_contains($context, 'event-validation-') => 'event-validation',
        str_contains($context, 'event-livewire-') => 'event-livewire',
        str_contains($context, 'event-ui-') => 'event-interface',
        str_contains($context, 'event-i18n-') => 'event-localization',
        str_contains($context, 'event-factory-') => 'event-factory',
        str_contains($context, 'event-seed-') => 'event-seeding',
        str_contains($context, 'event-test-') => 'event-testing',
        str_contains($context, 'event-perf-') => 'event-performance',
        str_contains($context, 'event-docs-') => 'event-documentation',
        str_contains($context, 'implementation pass'), str_contains($context, 'definition of done'),
        str_contains($context, 'final-report') => 'event-quality-release',
        default => 'event-foundation',
    };
}

/** @param list<string> $sectionPath */
function medicalDomainForRequirement(array $sectionPath, string $verbatim): string
{
    $context = strtolower(implode(' ', $sectionPath).' '.$verbatim);
    preg_match_all('/(?:^|\s)(\d{1,3})\s*[—-]/u', $context, $sectionNumbers);
    $sectionNumber = isset($sectionNumbers[1]) && $sectionNumbers[1] !== []
        ? (int) end($sectionNumbers[1])
        : null;

    return match (true) {
        $sectionNumber !== null && $sectionNumber >= 1 && $sectionNumber <= 15 => 'medical-foundation',
        $sectionNumber !== null && $sectionNumber >= 16 && $sectionNumber <= 35 => 'medical-creation-identity',
        $sectionNumber !== null && $sectionNumber >= 36 && $sectionNumber <= 60 => 'medical-access-consent',
        $sectionNumber !== null && $sectionNumber >= 61 && $sectionNumber <= 75 => 'medical-clinical-case',
        $sectionNumber !== null && $sectionNumber >= 76 && $sectionNumber <= 95 => 'medical-observation',
        $sectionNumber !== null && $sectionNumber >= 96 && $sectionNumber <= 125 => 'medical-allergy-medication',
        $sectionNumber !== null && $sectionNumber >= 126 && $sectionNumber <= 158 => 'medical-vaccination-lab',
        $sectionNumber !== null && $sectionNumber >= 159 && $sectionNumber <= 188 => 'medical-procedure-care-plan',
        $sectionNumber !== null && $sectionNumber >= 189 && $sectionNumber <= 220 => 'medical-emergency-provider-lifecycle',
        $sectionNumber !== null && $sectionNumber >= 221 && $sectionNumber <= 249 => 'medical-security-data-ai',
        $sectionNumber !== null && $sectionNumber >= 250 && $sectionNumber <= 270 => 'medical-interface-quality-release',
        $sectionNumber !== null && $sectionNumber >= 271 && $sectionNumber <= 295 => 'medical-scenario',
        str_contains($context, 'dostup') || str_contains($context, 'soglas') => 'medical-access-consent',
        str_contains($context, 'allerg') || str_contains($context, 'lekarstv') => 'medical-allergy-medication',
        str_contains($context, 'vakcin') || str_contains($context, 'laborator') => 'medical-vaccination-lab',
        str_contains($context, 'audit') || str_contains($context, 'shifrovan') => 'medical-security-data-ai',
        default => 'medical-foundation',
    };
}

/** @param list<string> $sectionPath */
function communityDomainForRequirement(array $sectionPath, string $verbatim): string
{
    $context = strtolower(implode(' ', $sectionPath).' '.$verbatim);
    preg_match_all('/(?:^|\s)(\d{1,3})\s*[—-]/u', $context, $sectionNumbers);
    $sectionNumber = isset($sectionNumbers[1]) && $sectionNumbers[1] !== []
        ? (int) end($sectionNumbers[1])
        : null;

    return match (true) {
        $sectionNumber !== null && $sectionNumber >= 1 && $sectionNumber <= 5 => 'community-foundation',
        $sectionNumber !== null && $sectionNumber >= 6 && $sectionNumber <= 17 => 'community-types',
        $sectionNumber !== null && $sectionNumber >= 18 && $sectionNumber <= 22 => 'community-structure',
        $sectionNumber !== null && $sectionNumber >= 23 && $sectionNumber <= 38 => 'community-creation-privacy',
        $sectionNumber !== null && $sectionNumber >= 39 && $sectionNumber <= 52 => 'community-membership',
        $sectionNumber !== null && $sectionNumber >= 53 && $sectionNumber <= 69 => 'community-roles',
        $sectionNumber !== null && $sectionNumber >= 70 && $sectionNumber <= 80 => 'community-governance',
        $sectionNumber !== null && $sectionNumber >= 81 && $sectionNumber <= 92 => 'community-forum',
        $sectionNumber !== null && $sectionNumber >= 93 && $sectionNumber <= 103 => 'community-knowledge',
        $sectionNumber !== null && $sectionNumber >= 104 && $sectionNumber <= 123 => 'community-operations',
        $sectionNumber !== null && $sectionNumber >= 124 && $sectionNumber <= 142 => 'community-safety-moderation',
        $sectionNumber !== null && $sectionNumber >= 143 && $sectionNumber <= 157 => 'community-discovery-localization',
        $sectionNumber !== null && $sectionNumber >= 158 && $sectionNumber <= 170 => 'community-interface-reputation',
        $sectionNumber !== null && $sectionNumber >= 171 && $sectionNumber <= 192 => 'community-lifecycle-data',
        $sectionNumber !== null && $sectionNumber >= 193 && $sectionNumber <= 203 => 'community-quality-release',
        $sectionNumber !== null && $sectionNumber >= 204 && $sectionNumber <= 220 => 'community-scenario',
        str_contains($context, 'moderac') || str_contains($context, 'bezopasn') || str_contains($context, 'zhalob') => 'community-safety-moderation',
        str_contains($context, 'rol') || str_contains($context, 'prav') => 'community-roles',
        str_contains($context, 'znan') || str_contains($context, 'faq') => 'community-knowledge',
        str_contains($context, 'sobyt') || str_contains($context, 'volonter') => 'community-operations',
        str_contains($context, 'poisk') || str_contains($context, 'perevod') => 'community-discovery-localization',
        str_contains($context, 'audit') || str_contains($context, 'texnicesk') => 'community-lifecycle-data',
        default => 'community-foundation',
    };
}

/** @param list<string> $sectionPath */
function communicationDomainForRequirement(array $sectionPath, string $verbatim): string
{
    $context = strtolower(implode(' ', $sectionPath).' '.$verbatim);
    preg_match_all('/(?:^|\s)(\d{1,3})\s*[—-]/u', $context, $sectionNumbers);
    $sectionNumber = isset($sectionNumbers[1]) && $sectionNumbers[1] !== []
        ? (int) end($sectionNumbers[1])
        : null;

    return match (true) {
        $sectionNumber !== null && $sectionNumber >= 1 && $sectionNumber <= 7 => 'communication-foundation',
        $sectionNumber !== null && $sectionNumber >= 8 && $sectionNumber <= 18 => 'communication-dialog',
        $sectionNumber !== null && $sectionNumber >= 19 && $sectionNumber <= 34 => 'communication-contact',
        $sectionNumber !== null && $sectionNumber >= 35 && $sectionNumber <= 40 => 'communication-data',
        $sectionNumber !== null && $sectionNumber >= 41 && $sectionNumber <= 61 => 'communication-message',
        $sectionNumber !== null && $sectionNumber >= 62 && $sectionNumber <= 80 => 'communication-media',
        $sectionNumber !== null && $sectionNumber >= 81 && $sectionNumber <= 101 => 'communication-sharing',
        $sectionNumber !== null && $sectionNumber >= 102 && $sectionNumber <= 117 => 'communication-encryption',
        $sectionNumber !== null && $sectionNumber >= 118 && $sectionNumber <= 145 => 'communication-group',
        $sectionNumber !== null && $sectionNumber >= 146 && $sectionNumber <= 166 => 'communication-call',
        $sectionNumber !== null && $sectionNumber >= 167 && $sectionNumber <= 184 => 'communication-workflow',
        $sectionNumber !== null && $sectionNumber >= 185 && $sectionNumber <= 203 => 'communication-safety-ai',
        $sectionNumber !== null && $sectionNumber >= 204 && $sectionNumber <= 222 => 'communication-control',
        $sectionNumber !== null && $sectionNumber >= 223 && $sectionNumber <= 241 => 'communication-interface-data',
        $sectionNumber !== null && $sectionNumber >= 242 && $sectionNumber <= 252 => 'communication-quality-release',
        $sectionNumber !== null && $sectionNumber >= 253 && $sectionNumber <= 280 => 'communication-scenario',
        str_contains($context, 'shifrovan') || str_contains($context, 'e2ee') => 'communication-encryption',
        str_contains($context, 'zvon') || str_contains($context, 'audio') || str_contains($context, 'video') => 'communication-call',
        str_contains($context, 'bezopasn') || str_contains($context, 'moshenn') || str_contains($context, 'zhalob') => 'communication-safety-ai',
        str_contains($context, 'grupp') => 'communication-group',
        str_contains($context, 'audit') || str_contains($context, 'texnicesk') => 'communication-interface-data',
        default => 'communication-foundation',
    };
}

/** @param list<string> $sectionPath */
function contentDomainForRequirement(array $sectionPath, string $verbatim): string
{
    $context = strtolower(implode(' ', $sectionPath).' '.$verbatim);
    preg_match_all('/(?:^|\s)(\d{1,3})\s*[—-]/u', $context, $sectionNumbers);
    $sectionNumber = isset($sectionNumbers[1]) && $sectionNumbers[1] !== []
        ? (int) end($sectionNumbers[1])
        : null;

    return match (true) {
        $sectionNumber !== null && $sectionNumber >= 1 && $sectionNumber <= 12 => 'content-foundation',
        $sectionNumber !== null && $sectionNumber >= 13 && $sectionNumber <= 33 => 'content-types',
        $sectionNumber !== null && $sectionNumber >= 34 && $sectionNumber <= 50 => 'content-authoring',
        $sectionNumber !== null && $sectionNumber >= 51 && $sectionNumber <= 72 => 'content-media',
        $sectionNumber !== null && $sectionNumber >= 73 && $sectionNumber <= 79 => 'content-stories',
        $sectionNumber !== null && $sectionNumber >= 80 && $sectionNumber <= 103 => 'content-feed',
        $sectionNumber !== null && $sectionNumber >= 104 && $sectionNumber <= 110 => 'content-reaction',
        $sectionNumber !== null && $sectionNumber >= 111 && $sectionNumber <= 125 => 'content-comment',
        $sectionNumber !== null && $sectionNumber >= 126 && $sectionNumber <= 130 => 'content-mention',
        $sectionNumber !== null && $sectionNumber >= 131 && $sectionNumber <= 137 => 'content-hashtag',
        $sectionNumber !== null && $sectionNumber >= 138 && $sectionNumber <= 146 => 'content-sharing',
        $sectionNumber !== null && $sectionNumber >= 147 && $sectionNumber <= 156 => 'content-library-notification',
        $sectionNumber !== null && $sectionNumber >= 157 && $sectionNumber <= 168 => 'content-lifecycle-rights',
        $sectionNumber !== null && $sectionNumber >= 169 && $sectionNumber <= 179 => 'content-localization-interface',
        $sectionNumber !== null && $sectionNumber >= 180 && $sectionNumber <= 184 => 'content-offline',
        $sectionNumber !== null && $sectionNumber >= 185 && $sectionNumber <= 207 => 'content-safety-moderation',
        $sectionNumber !== null && $sectionNumber >= 208 && $sectionNumber <= 216 => 'content-ai',
        $sectionNumber !== null && $sectionNumber >= 217 && $sectionNumber <= 227 => 'content-integration',
        $sectionNumber !== null && $sectionNumber >= 228 && $sectionNumber <= 242 => 'content-data',
        $sectionNumber !== null && $sectionNumber >= 243 && $sectionNumber <= 252 => 'content-quality',
        $sectionNumber !== null && $sectionNumber >= 253 && $sectionNumber <= 259 => 'content-release',
        $sectionNumber !== null && $sectionNumber >= 260 && $sectionNumber <= 287 => 'content-scenario',
        str_contains($context, 'moderac') || str_contains($context, 'bezopasn') => 'content-safety-moderation',
        str_contains($context, 'media') || str_contains($context, 'video') || str_contains($context, 'fotograf') => 'content-media',
        str_contains($context, 'lenta') || str_contains($context, 'rekomendac') => 'content-feed',
        str_contains($context, 'perevod') || str_contains($context, 'dostupnost') => 'content-localization-interface',
        str_contains($context, 'audit') || str_contains($context, 'texnicesk') => 'content-data',
        default => 'content-foundation',
    };
}

/** @param list<string> $sectionPath */
function socialDomainForRequirement(array $sectionPath, string $verbatim): string
{
    $context = strtolower(implode(' ', $sectionPath).' '.$verbatim);
    preg_match_all('/(?:^|\s)(\d{1,3})\s*[—-]/u', $context, $sectionNumbers);
    $sectionNumber = isset($sectionNumbers[1]) && $sectionNumbers[1] !== []
        ? (int) end($sectionNumbers[1])
        : null;

    return match (true) {
        $sectionNumber !== null && $sectionNumber >= 1 && $sectionNumber <= 15 => 'social-relationship',
        $sectionNumber !== null && $sectionNumber >= 16 && $sectionNumber <= 35 => 'social-request',
        $sectionNumber !== null && $sectionNumber >= 36 && $sectionNumber <= 45 => 'social-follow',
        $sectionNumber !== null && $sectionNumber >= 46 && $sectionNumber <= 56 => 'social-pet-friendship',
        $sectionNumber !== null && $sectionNumber >= 57 && $sectionNumber <= 70 => 'social-meetup',
        $sectionNumber !== null && $sectionNumber >= 71 && $sectionNumber <= 89 => 'social-recommendation',
        $sectionNumber !== null && $sectionNumber >= 90 && $sectionNumber <= 100 => 'social-search',
        $sectionNumber !== null && $sectionNumber >= 101 && $sectionNumber <= 111 => 'social-messaging',
        $sectionNumber !== null && $sectionNumber >= 112 && $sectionNumber <= 121 => 'social-safety',
        $sectionNumber !== null && $sectionNumber >= 122 && $sectionNumber <= 132 => 'social-groups-events',
        $sectionNumber !== null && $sectionNumber >= 133 && $sectionNumber <= 141 => 'social-privacy-notifications',
        $sectionNumber !== null && $sectionNumber >= 142 && $sectionNumber <= 153 => 'social-moderation',
        $sectionNumber !== null && $sectionNumber >= 154 && $sectionNumber <= 167 => 'social-data',
        $sectionNumber !== null && $sectionNumber >= 168 && $sectionNumber <= 177 => 'social-interface',
        $sectionNumber !== null && $sectionNumber >= 178 && $sectionNumber <= 180 => 'social-localization',
        $sectionNumber !== null && $sectionNumber >= 181 && $sectionNumber <= 185 => 'social-quality',
        $sectionNumber !== null && $sectionNumber >= 186 && $sectionNumber <= 190 => 'social-release',
        $sectionNumber !== null && $sectionNumber >= 191 && $sectionNumber <= 208 => 'social-scenario',
        str_contains($context, 'blokirov') || str_contains($context, 'bezopasn') => 'social-safety',
        str_contains($context, 'rekomendac') || str_contains($context, 'poisk') => 'social-recommendation',
        str_contains($context, 'perevod') || str_contains($context, 'mnogoiazy') => 'social-localization',
        str_contains($context, 'audit') || str_contains($context, 'texnicesk') => 'social-data',
        default => 'social-relationship',
    };
}

/** @param list<string> $sectionPath */
function petDomainForRequirement(array $sectionPath, string $verbatim): string
{
    $context = strtolower(implode(' ', $sectionPath).' '.$verbatim);
    preg_match_all('/(?:^|\s)(\d{1,3})\s*[—-]/u', $context, $sectionNumbers);
    $sectionNumber = isset($sectionNumbers[1]) && $sectionNumbers[1] !== []
        ? (int) end($sectionNumbers[1])
        : null;

    return match (true) {
        $sectionNumber !== null && $sectionNumber >= 10 && $sectionNumber <= 20 => 'pet-creation',
        $sectionNumber !== null && $sectionNumber >= 21 && $sectionNumber <= 41 => 'pet-identity',
        $sectionNumber !== null && $sectionNumber >= 42 && $sectionNumber <= 51 => 'pet-media',
        $sectionNumber !== null && $sectionNumber >= 52 && $sectionNumber <= 67 => 'pet-behavior',
        $sectionNumber !== null && $sectionNumber >= 68 && $sectionNumber <= 75 => 'pet-public-profile',
        $sectionNumber !== null && $sectionNumber >= 76 && $sectionNumber <= 87 => 'pet-privacy',
        $sectionNumber !== null && $sectionNumber >= 88 && $sectionNumber <= 106 => 'pet-ownership',
        $sectionNumber !== null && $sectionNumber >= 107 && $sectionNumber <= 126 => 'pet-social',
        $sectionNumber !== null && $sectionNumber >= 127 && $sectionNumber <= 142 => 'pet-integration',
        $sectionNumber !== null && $sectionNumber >= 143 && $sectionNumber <= 147 => 'pet-lifecycle',
        $sectionNumber !== null && $sectionNumber >= 148 && $sectionNumber <= 151 => 'pet-discovery',
        $sectionNumber !== null && $sectionNumber >= 152 && $sectionNumber <= 155 => 'pet-localization',
        $sectionNumber !== null && $sectionNumber >= 156 && $sectionNumber <= 165 => 'pet-interface',
        $sectionNumber !== null && $sectionNumber >= 166 && $sectionNumber <= 174 => 'pet-lifecycle',
        $sectionNumber !== null && $sectionNumber >= 175 && $sectionNumber <= 183 => 'pet-moderation',
        $sectionNumber !== null && $sectionNumber >= 184 && $sectionNumber <= 198 => 'pet-data',
        $sectionNumber !== null && $sectionNumber >= 199 && $sectionNumber <= 200 => 'pet-release',
        $sectionNumber !== null && $sectionNumber >= 201 && $sectionNumber <= 205 => 'pet-quality',
        $sectionNumber !== null && $sectionNumber >= 206 && $sectionNumber <= 219 => 'pet-scenario',
        str_contains($context, 'privatnost') => 'pet-privacy',
        str_contains($context, 'vladen') || str_contains($context, 'sovladel') => 'pet-ownership',
        str_contains($context, 'media') || str_contains($context, 'fotograf') => 'pet-media',
        str_contains($context, 'moderac') || str_contains($context, 'zhalob') => 'pet-moderation',
        str_contains($context, 'dostupnost') || str_contains($context, 'mobiln') || str_contains($context, 'desktop') => 'pet-interface',
        str_contains($context, 'perevod') || str_contains($context, 'mnogoiazy') => 'pet-localization',
        str_contains($context, 'audit') || str_contains($context, 'texnicesk') => 'pet-data',
        default => 'pet-profile',
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
        'pet-profile' => 'pet.profile',
        'pet-creation' => 'pet.creation',
        'pet-identity' => 'pet.identity',
        'pet-media' => 'pet.media',
        'pet-behavior' => 'pet.behavior',
        'pet-public-profile' => 'pet.public-profile',
        'pet-privacy' => 'pet.privacy',
        'pet-ownership' => 'pet.ownership',
        'pet-social' => 'pet.social',
        'pet-integration' => 'pet.integration',
        'pet-lifecycle' => 'pet.lifecycle',
        'pet-discovery' => 'pet.discovery',
        'pet-localization' => 'pet.translation',
        'pet-interface' => 'pet.interface',
        'pet-moderation' => 'pet.moderation',
        'pet-data' => 'pet.data',
        'pet-release' => 'pet.release',
        'pet-quality' => 'pet.quality',
        'pet-scenario' => 'pet.scenario',
        'social-relationship' => 'social.relationship',
        'social-request' => 'social.request',
        'social-follow' => 'social.follow',
        'social-pet-friendship' => 'social.pet-friendship',
        'social-meetup' => 'social.meetup',
        'social-recommendation' => 'social.recommendation',
        'social-search' => 'social.search',
        'social-messaging' => 'social.messaging',
        'social-safety' => 'social.safety',
        'social-groups-events' => 'social.groups-events',
        'social-privacy-notifications' => 'social.privacy-notifications',
        'social-moderation' => 'social.moderation',
        'social-data' => 'social.data',
        'social-interface' => 'social.interface',
        'social-localization' => 'social.translation',
        'social-quality' => 'social.quality',
        'social-release' => 'social.release',
        'social-scenario' => 'social.scenario',
        'content-foundation' => 'content.foundation',
        'content-types' => 'content.type',
        'content-authoring' => 'content.authoring',
        'content-media' => 'content.media',
        'content-stories' => 'content.story',
        'content-feed' => 'content.feed',
        'content-reaction' => 'content.reaction',
        'content-comment' => 'content.comment',
        'content-mention' => 'content.mention',
        'content-hashtag' => 'content.hashtag',
        'content-sharing' => 'content.sharing',
        'content-library-notification' => 'content.library-notification',
        'content-lifecycle-rights' => 'content.lifecycle-rights',
        'content-localization-interface' => 'content.translation-interface',
        'content-offline' => 'content.offline',
        'content-safety-moderation' => 'content.safety-moderation',
        'content-ai' => 'content.ai',
        'content-integration' => 'content.integration',
        'content-data' => 'content.data',
        'content-quality' => 'content.quality',
        'content-release' => 'content.release',
        'content-scenario' => 'content.scenario',
        'communication-foundation' => 'communication.foundation',
        'communication-dialog' => 'communication.dialog',
        'communication-contact' => 'communication.contact',
        'communication-data' => 'communication.data',
        'communication-message' => 'communication.message',
        'communication-media' => 'communication.media',
        'communication-sharing' => 'communication.sharing',
        'communication-encryption' => 'communication.encryption',
        'communication-group' => 'communication.group',
        'communication-call' => 'communication.call',
        'communication-workflow' => 'communication.workflow',
        'communication-safety-ai' => 'communication.safety-ai',
        'communication-control' => 'communication.control',
        'communication-interface-data' => 'communication.interface-data',
        'communication-quality-release' => 'communication.quality-release',
        'communication-scenario' => 'communication.scenario',
        'community-foundation' => 'community.foundation',
        'community-types' => 'community.type',
        'community-structure' => 'community.structure',
        'community-creation-privacy' => 'community.creation-privacy',
        'community-membership' => 'community.membership',
        'community-roles' => 'community.role',
        'community-governance' => 'community.governance',
        'community-forum' => 'community.forum',
        'community-knowledge' => 'community.knowledge',
        'community-operations' => 'community.operations',
        'community-safety-moderation' => 'community.safety-moderation',
        'community-discovery-localization' => 'community.discovery-localization',
        'community-interface-reputation' => 'community.interface-reputation',
        'community-lifecycle-data' => 'community.lifecycle-data',
        'community-quality-release' => 'community.quality-release',
        'community-scenario' => 'community.scenario',
        'medical-foundation' => 'medical.foundation',
        'medical-creation-identity' => 'medical.creation-identity',
        'medical-access-consent' => 'medical.access-consent',
        'medical-clinical-case' => 'medical.clinical-case',
        'medical-observation' => 'medical.observation',
        'medical-allergy-medication' => 'medical.allergy-medication',
        'medical-vaccination-lab' => 'medical.vaccination-lab',
        'medical-procedure-care-plan' => 'medical.procedure-care-plan',
        'medical-emergency-provider-lifecycle' => 'medical.emergency-provider-lifecycle',
        'medical-security-data-ai' => 'medical.security-data-ai',
        'medical-interface-quality-release' => 'medical.interface-quality-release',
        'medical-scenario' => 'medical.scenario',
        'forum-feature' => 'forum.feature',
        default => str_replace('-', '.', $domain),
    };
}

function phaseForRequirement(
    string $domain,
    string $sourceSection,
    string $verbatim,
    string $sourcePart,
): int {
    $context = strtolower($sourceSection.' '.$verbatim);

    if ($sourcePart === 'pet-profile-revision') {
        return match ($domain) {
            'pet-profile', 'pet-release' => 17,
            'pet-creation', 'pet-identity', 'pet-data' => 18,
            'pet-ownership', 'pet-privacy' => 19,
            'pet-media', 'pet-behavior', 'pet-social', 'pet-public-profile' => 20,
            'pet-integration', 'pet-discovery' => 21,
            'pet-lifecycle' => 22,
            'pet-moderation' => 23,
            'pet-localization', 'pet-interface' => 24,
            'pet-quality', 'pet-scenario' => 25,
            default => 16,
        };
    }

    if ($sourcePart === 'social-relationships-revision') {
        return match ($domain) {
            'social-relationship', 'social-follow', 'social-data' => 27,
            'social-request', 'social-safety', 'social-moderation' => 28,
            'social-pet-friendship', 'social-meetup' => 29,
            'social-recommendation', 'social-search' => 30,
            'social-messaging', 'social-groups-events', 'social-privacy-notifications' => 31,
            'social-interface', 'social-localization' => 32,
            'social-release', 'social-quality' => 33,
            'social-scenario' => 34,
            default => 26,
        };
    }

    if ($sourcePart === 'content-feed-revision') {
        return match ($domain) {
            'content-foundation', 'content-types', 'content-data' => 36,
            'content-authoring', 'content-lifecycle-rights' => 37,
            'content-stories', 'content-feed', 'content-reaction', 'content-comment',
            'content-mention', 'content-hashtag', 'content-sharing',
            'content-library-notification' => 38,
            'content-media' => 39,
            'content-quality' => 40,
            'content-safety-moderation' => 41,
            'content-ai', 'content-integration' => 42,
            'content-localization-interface', 'content-offline' => 43,
            'content-release', 'content-scenario' => 44,
            default => 35,
        };
    }

    if ($sourcePart === 'communication-revision') {
        return match ($domain) {
            'communication-foundation', 'communication-dialog', 'communication-contact',
            'communication-data' => 46,
            'communication-message', 'communication-workflow' => 47,
            'communication-media', 'communication-sharing' => 48,
            'communication-group' => 49,
            'communication-call' => 50,
            'communication-encryption' => 51,
            'communication-safety-ai', 'communication-control' => 52,
            'communication-interface-data' => 53,
            'communication-quality-release', 'communication-scenario' => 54,
            default => 45,
        };
    }

    if ($sourcePart === 'community-revision') {
        return match ($domain) {
            'community-foundation', 'community-types', 'community-structure' => 55,
            'community-creation-privacy', 'community-membership', 'community-roles' => 56,
            'community-governance', 'community-forum' => 57,
            'community-knowledge' => 58,
            'community-operations' => 59,
            'community-safety-moderation' => 60,
            'community-discovery-localization', 'community-interface-reputation' => 61,
            'community-lifecycle-data' => 62,
            'community-quality-release', 'community-scenario' => 63,
            default => 55,
        };
    }

    if ($sourcePart === 'medical-record-revision') {
        return match ($domain) {
            'medical-foundation', 'medical-creation-identity' => 64,
            'medical-access-consent' => 65,
            'medical-clinical-case', 'medical-observation' => 66,
            'medical-allergy-medication' => 67,
            'medical-vaccination-lab' => 68,
            'medical-procedure-care-plan' => 69,
            'medical-emergency-provider-lifecycle' => 70,
            'medical-security-data-ai' => 71,
            'medical-interface-quality-release' => 72,
            'medical-scenario' => 73,
            default => 64,
        };
    }

    if ($sourcePart === 'portal-architecture-revision') {
        return 74;
    }

    if ($sourcePart === 'event-lifecycle-revision') {
        return 75;
    }

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
        in_array($domain, [
            'security-and-privacy',
            'moderation',
            'pet-privacy',
            'pet-moderation',
            'pet-ownership',
            'social-safety',
            'social-moderation',
            'social-privacy-notifications',
            'content-safety-moderation',
            'communication-encryption',
            'communication-safety-ai',
            'communication-control',
            'community-creation-privacy',
            'community-roles',
            'community-safety-moderation',
            'medical-access-consent',
            'medical-allergy-medication',
            'medical-emergency-provider-lifecycle',
            'medical-security-data-ai',
        ], true)
        || str_contains($value, 'must not')
        || str_contains($value, 'never')
        || str_contains($value, 'critical')
        || str_contains($value, 'private')
    ) {
        return 'critical';
    }

    return in_array($domain, [
        'persistence',
        'animal-taxonomy',
        'testing-and-traceability',
        'pet-data',
        'pet-identity',
        'pet-lifecycle',
        'social-data',
        'social-request',
        'content-foundation',
        'content-data',
        'communication-foundation',
        'communication-data',
        'communication-interface-data',
        'community-foundation',
        'community-membership',
        'community-governance',
        'community-lifecycle-data',
        'medical-foundation',
        'medical-creation-identity',
        'medical-clinical-case',
        'medical-security-data-ai',
    ], true)
        ? 'high'
        : 'standard';
}

function impactFor(string $domain, string $impact): string
{
    $socialDomains = [
        'social-relationship',
        'social-request',
        'social-follow',
        'social-pet-friendship',
        'social-meetup',
        'social-recommendation',
        'social-search',
        'social-messaging',
        'social-safety',
        'social-groups-events',
        'social-privacy-notifications',
        'social-moderation',
        'social-data',
        'social-interface',
        'social-localization',
        'social-quality',
        'social-release',
        'social-scenario',
    ];
    $contentDomains = [
        'content-foundation',
        'content-types',
        'content-authoring',
        'content-media',
        'content-stories',
        'content-feed',
        'content-reaction',
        'content-comment',
        'content-mention',
        'content-hashtag',
        'content-sharing',
        'content-library-notification',
        'content-lifecycle-rights',
        'content-localization-interface',
        'content-offline',
        'content-safety-moderation',
        'content-ai',
        'content-integration',
        'content-data',
        'content-quality',
        'content-release',
        'content-scenario',
    ];
    $communicationDomains = [
        'communication-foundation',
        'communication-dialog',
        'communication-contact',
        'communication-data',
        'communication-message',
        'communication-media',
        'communication-sharing',
        'communication-encryption',
        'communication-group',
        'communication-call',
        'communication-workflow',
        'communication-safety-ai',
        'communication-control',
        'communication-interface-data',
        'communication-quality-release',
        'communication-scenario',
    ];
    $communityDomains = [
        'community-foundation',
        'community-types',
        'community-structure',
        'community-creation-privacy',
        'community-membership',
        'community-roles',
        'community-governance',
        'community-forum',
        'community-knowledge',
        'community-operations',
        'community-safety-moderation',
        'community-discovery-localization',
        'community-interface-reputation',
        'community-lifecycle-data',
        'community-quality-release',
        'community-scenario',
    ];
    $medicalDomains = [
        'medical-foundation',
        'medical-creation-identity',
        'medical-access-consent',
        'medical-clinical-case',
        'medical-observation',
        'medical-allergy-medication',
        'medical-vaccination-lab',
        'medical-procedure-care-plan',
        'medical-emergency-provider-lifecycle',
        'medical-security-data-ai',
        'medical-interface-quality-release',
        'medical-scenario',
    ];
    $relevant = [
        'database' => ['animal-taxonomy', 'moderation', 'reputation-and-trust', 'forum-category', 'persistence', 'forum-feature', 'pet-profile', 'pet-creation', 'pet-identity', 'pet-media', 'pet-behavior', 'pet-public-profile', 'pet-privacy', 'pet-ownership', 'pet-social', 'pet-integration', 'pet-lifecycle', 'pet-discovery', 'pet-moderation', 'pet-data', 'pet-release', 'pet-quality'],
        'backend' => ['animal-taxonomy', 'moderation', 'reputation-and-trust', 'forum-category', 'search-and-discovery', 'persistence', 'forum-feature', 'pet-profile', 'pet-creation', 'pet-identity', 'pet-media', 'pet-behavior', 'pet-public-profile', 'pet-privacy', 'pet-ownership', 'pet-social', 'pet-integration', 'pet-lifecycle', 'pet-discovery', 'pet-localization', 'pet-interface', 'pet-moderation', 'pet-data', 'pet-release', 'pet-quality'],
        'livewire' => ['interface', 'forum-category', 'search-and-discovery', 'moderation', 'animal-taxonomy', 'forum-feature', 'pet-creation', 'pet-identity', 'pet-media', 'pet-behavior', 'pet-public-profile', 'pet-privacy', 'pet-ownership', 'pet-social', 'pet-integration', 'pet-lifecycle', 'pet-discovery', 'pet-localization', 'pet-interface', 'pet-moderation', 'pet-release'],
        'interface' => ['interface', 'forum-category', 'search-and-discovery', 'moderation', 'animal-taxonomy', 'forum-feature', 'pet-profile', 'pet-creation', 'pet-identity', 'pet-media', 'pet-behavior', 'pet-public-profile', 'pet-privacy', 'pet-ownership', 'pet-social', 'pet-integration', 'pet-lifecycle', 'pet-discovery', 'pet-localization', 'pet-interface', 'pet-moderation', 'pet-release', 'pet-quality'],
        'authorization' => ['security-and-privacy', 'moderation', 'reputation-and-trust', 'animal-taxonomy', 'forum-feature', 'pet-creation', 'pet-identity', 'pet-media', 'pet-public-profile', 'pet-privacy', 'pet-ownership', 'pet-social', 'pet-integration', 'pet-lifecycle', 'pet-moderation', 'pet-data'],
        'privacy' => ['security-and-privacy', 'moderation', 'search-and-discovery', 'forum-feature', 'pet-profile', 'pet-creation', 'pet-identity', 'pet-media', 'pet-behavior', 'pet-public-profile', 'pet-privacy', 'pet-ownership', 'pet-social', 'pet-integration', 'pet-lifecycle', 'pet-discovery', 'pet-localization', 'pet-interface', 'pet-moderation', 'pet-data', 'pet-quality'],
        'security' => ['security-and-privacy', 'moderation', 'reputation-and-trust', 'interface', 'forum-feature', 'pet-profile', 'pet-creation', 'pet-identity', 'pet-media', 'pet-public-profile', 'pet-privacy', 'pet-ownership', 'pet-social', 'pet-integration', 'pet-lifecycle', 'pet-discovery', 'pet-interface', 'pet-moderation', 'pet-data', 'pet-release'],
        'moderation' => ['moderation', 'reputation-and-trust', 'forum-category', 'forum-feature', 'pet-media', 'pet-behavior', 'pet-public-profile', 'pet-social', 'pet-lifecycle', 'pet-moderation'],
        'cache' => ['animal-taxonomy', 'forum-category', 'search-and-discovery', 'reputation-and-trust', 'pet-public-profile', 'pet-privacy', 'pet-social', 'pet-integration', 'pet-lifecycle', 'pet-discovery', 'pet-localization', 'pet-data'],
        'migration' => ['animal-taxonomy', 'moderation', 'reputation-and-trust', 'forum-category', 'persistence', 'forum-feature', 'pet-profile', 'pet-creation', 'pet-identity', 'pet-media', 'pet-behavior', 'pet-public-profile', 'pet-privacy', 'pet-ownership', 'pet-social', 'pet-integration', 'pet-lifecycle', 'pet-discovery', 'pet-moderation', 'pet-data', 'pet-release', 'pet-quality'],
        'seed' => ['animal-taxonomy', 'moderation', 'reputation-and-trust', 'forum-category', 'seeding', 'pet-profile', 'pet-identity', 'pet-behavior', 'pet-privacy', 'pet-ownership', 'pet-lifecycle', 'pet-moderation'],
        'factory' => ['animal-taxonomy', 'moderation', 'reputation-and-trust', 'forum-category', 'persistence', 'forum-feature', 'pet-profile', 'pet-creation', 'pet-identity', 'pet-media', 'pet-behavior', 'pet-public-profile', 'pet-privacy', 'pet-ownership', 'pet-social', 'pet-integration', 'pet-lifecycle', 'pet-discovery', 'pet-moderation', 'pet-data'],
    ];

    foreach (['database', 'backend', 'migration', 'factory'] as $key) {
        $relevant[$key] = array_merge(
            $relevant[$key],
            $socialDomains,
            $contentDomains,
            $communicationDomains,
            $communityDomains,
            $medicalDomains,
        );
    }

    foreach (['livewire', 'interface'] as $key) {
        $relevant[$key] = array_merge(
            $relevant[$key],
            array_diff($socialDomains, ['social-data']),
            array_diff($contentDomains, ['content-data']),
            array_diff($communicationDomains, ['communication-data']),
            array_diff($communityDomains, ['community-lifecycle-data']),
            array_diff($medicalDomains, ['medical-security-data-ai']),
        );
    }

    foreach (['authorization', 'privacy', 'security', 'cache'] as $key) {
        $relevant[$key] = array_merge(
            $relevant[$key],
            array_diff($socialDomains, ['social-localization', 'social-scenario']),
            array_diff($contentDomains, ['content-localization-interface', 'content-scenario']),
            array_diff($communicationDomains, ['communication-scenario']),
            array_diff($communityDomains, ['community-discovery-localization', 'community-scenario']),
            array_diff($medicalDomains, ['medical-interface-quality-release', 'medical-scenario']),
        );
    }

    $relevant['moderation'] = array_merge($relevant['moderation'], [
        'social-request',
        'social-messaging',
        'social-safety',
        'social-moderation',
        'content-types',
        'content-media',
        'content-comment',
        'content-mention',
        'content-sharing',
        'content-safety-moderation',
        'content-ai',
        'communication-contact',
        'communication-message',
        'communication-media',
        'communication-sharing',
        'communication-group',
        'communication-call',
        'communication-safety-ai',
        'communication-control',
        'community-membership',
        'community-roles',
        'community-governance',
        'community-forum',
        'community-knowledge',
        'community-operations',
        'community-safety-moderation',
        'medical-access-consent',
        'medical-security-data-ai',
    ]);

    $relevant['seed'] = array_merge($relevant['seed'], [
        'social-relationship',
        'social-follow',
        'social-safety',
        'social-release',
        'content-foundation',
        'content-types',
        'content-safety-moderation',
        'content-release',
        'communication-foundation',
        'communication-dialog',
        'communication-safety-ai',
        'communication-quality-release',
        'community-foundation',
        'community-types',
        'community-membership',
        'community-safety-moderation',
        'community-quality-release',
        'medical-foundation',
        'medical-creation-identity',
        'medical-interface-quality-release',
    ]);

    return in_array($domain, $relevant[$impact] ?? [], true)
        ? 'Requires phase-specific analysis and evidence.'
        : 'No direct impact identified during atomic extraction.';
}

function escapeTable(string $value): string
{
    return str_replace(['|', "\n"], ['\\|', ' '], $value);
}
