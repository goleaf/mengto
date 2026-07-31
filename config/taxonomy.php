<?php

declare(strict_types=1);

return [
    'snapshot_disk' => 'local',
    'snapshot_directory' => 'taxonomy/snapshots',
    'chunk_size' => 500,
    'lock_seconds' => 120,
    'max_stored_issues' => 10_000,
    'tree_cache_seconds' => 3600,
    'column_candidates' => [
        'source_record_id' => ['taxonID', 'id'],
        'parent_source_record_id' => ['parentNameUsageID', 'parentID'],
        'accepted_source_record_id' => ['acceptedNameUsageID', 'acceptedID'],
        'scientific_name' => ['scientificName'],
        'canonical_name' => ['scientificNameWithoutAuthorship', 'canonicalName'],
        'authorship' => ['scientificNameAuthorship', 'authorship'],
        'rank' => ['taxonRank', 'rank'],
        'taxonomic_status' => ['taxonomicStatus', 'status'],
        'nomenclatural_code' => ['nomenclaturalCode'],
        'common_name' => ['vernacularName', 'commonName'],
        'language' => ['language'],
        'is_extinct' => ['isExtinct', 'extinct'],
        'is_marine' => ['isMarine', 'marine'],
        'is_freshwater' => ['isFreshwater', 'freshwater'],
        'is_terrestrial' => ['isTerrestrial', 'terrestrial'],
    ],
    'required_columns' => [
        'source_record_id',
        'scientific_name',
        'rank',
        'taxonomic_status',
    ],
];
