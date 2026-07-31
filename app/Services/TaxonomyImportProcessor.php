<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\TaxonomyRecord;
use App\Models\Taxon;
use App\Models\TaxonChange;
use App\Models\TaxonExternalIdentifier;
use App\Models\TaxonImport;
use App\Models\TaxonImportIssue;
use App\Models\TaxonName;
use App\Models\TaxonSource;
use App\Models\TaxonVersion;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class TaxonomyImportProcessor
{
    public function __construct(
        private TaxonIdentity $identity,
    ) {}

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{processed: int, inserted: int, updated: int, unchanged: int, synonyms: int, errors: int}
     */
    public function process(TaxonImport $import, TaxonSource $source, array $rows): array
    {
        [$records, $issues] = $this->mapRecords($rows);

        return DB::transaction(function () use ($import, $issues, $records, $rows, $source): array {
            $now = CarbonImmutable::now();
            $stableKeys = [];

            foreach ($records as $record) {
                foreach ([
                    $record->sourceRecordId,
                    $record->parentSourceRecordId,
                    $record->acceptedSourceRecordId,
                ] as $sourceRecordId) {
                    if ($sourceRecordId !== null) {
                        $stableKeys[$sourceRecordId] = $this->identity->stableKey(
                            $source,
                            $sourceRecordId,
                        );
                    }
                }
            }

            Taxon::query()->insertOrIgnore(array_map(
                static fn (string $stableKey): array => [
                    'stable_key' => $stableKey,
                    'resolution_status' => 'unresolved',
                    'requires_review' => true,
                    'is_active' => true,
                    'metadata' => json_encode([], JSON_THROW_ON_ERROR),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                array_values($stableKeys),
            ));

            /** @var Collection<string, Taxon> $taxa */
            $taxa = Taxon::query()
                ->whereIn('stable_key', array_values($stableKeys))
                ->get(['id', 'stable_key', 'resolution_status', 'accepted_taxon_id'])
                ->keyBy('stable_key');
            $taxonIds = [];

            foreach ($stableKeys as $sourceRecordId => $stableKey) {
                $taxonIds[$sourceRecordId] = $taxa->get($stableKey)?->id;
            }

            $sourceRecordIds = array_map(
                static fn (TaxonomyRecord $record): string => $record->sourceRecordId,
                $records,
            );
            $existingVersions = TaxonVersion::query()
                ->where('taxon_import_id', $import->id)
                ->whereIn('source_record_id', $sourceRecordIds)
                ->get()
                ->keyBy('source_record_id');
            $versionRows = [];
            $externalIdentifierRows = [];
            $nameRows = [];
            $taxonRows = [];
            $changeRows = [];
            $inserted = 0;
            $updated = 0;
            $unchanged = 0;
            $synonyms = 0;

            foreach ($records as $record) {
                $taxonId = (int) $taxonIds[$record->sourceRecordId];
                $isAccepted = in_array($record->taxonomicStatus, [
                    'accepted',
                    'provisionally accepted',
                    'valid',
                ], true);
                $acceptedTaxonId = $record->acceptedSourceRecordId !== null
                    ? ($taxonIds[$record->acceptedSourceRecordId] ?? null)
                    : null;

                if (! $isAccepted) {
                    $synonyms++;
                }

                $taxonRows[] = [
                    'id' => $taxonId,
                    'stable_key' => $stableKeys[$record->sourceRecordId],
                    'accepted_taxon_id' => $acceptedTaxonId,
                    'resolution_status' => $isAccepted ? 'accepted' : 'synonym',
                    'requires_review' => ! $isAccepted && $acceptedTaxonId === null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $version = [
                    'taxon_id' => $taxonId,
                    'taxon_import_id' => $import->id,
                    'taxon_source_id' => $source->id,
                    'parent_taxon_id' => $record->parentSourceRecordId !== null
                        ? ($taxonIds[$record->parentSourceRecordId] ?? null)
                        : null,
                    'source_record_id' => $record->sourceRecordId,
                    'rank' => (string) $record->rank,
                    'scientific_name' => $record->scientificName,
                    'canonical_name' => $record->canonicalName,
                    'normalized_scientific_name' => $this->identity->normalizeName(
                        $record->scientificName,
                    ),
                    'authorship' => $record->authorship,
                    'nomenclatural_code' => $record->nomenclaturalCode,
                    'taxonomic_status' => $record->taxonomicStatus,
                    'depth' => 0,
                    'hierarchy_path' => null,
                    'is_extinct' => $record->isExtinct,
                    'is_fossil' => false,
                    'is_marine' => $record->isMarine,
                    'is_freshwater' => $record->isFreshwater,
                    'is_terrestrial' => $record->isTerrestrial,
                    'has_domestic_relevance' => false,
                    'has_community_relevance' => false,
                    'is_active_version' => false,
                    'metadata' => json_encode([
                        'source_row' => $record->sourceRow,
                    ], JSON_THROW_ON_ERROR),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $existing = $existingVersions->get($record->sourceRecordId);
                $changed = $existing === null || $this->versionChanged($existing, $version);

                if ($existing === null) {
                    $inserted++;
                } elseif ($changed) {
                    $updated++;
                } else {
                    $unchanged++;
                }

                if ($changed) {
                    $changeRows[] = [
                        'taxon_id' => $taxonId,
                        'taxon_import_id' => $import->id,
                        'change_type' => $existing === null ? 'addition' : 'update',
                        'before' => $existing === null
                            ? null
                            : json_encode($existing->only([
                                'parent_taxon_id',
                                'rank',
                                'scientific_name',
                                'taxonomic_status',
                            ]), JSON_THROW_ON_ERROR),
                        'after' => json_encode([
                            'parent_taxon_id' => $version['parent_taxon_id'],
                            'rank' => $version['rank'],
                            'scientific_name' => $version['scientific_name'],
                            'taxonomic_status' => $version['taxonomic_status'],
                        ], JSON_THROW_ON_ERROR),
                        'reason_code' => 'source-import',
                        'metadata' => json_encode([], JSON_THROW_ON_ERROR),
                        'created_at' => $now,
                    ];
                }

                $versionRows[] = $version;
                $externalIdentifierRows[] = [
                    'taxon_id' => $taxonId,
                    'taxon_source_id' => $source->id,
                    'external_identifier' => $record->sourceRecordId,
                    'identifier_type' => 'source-record',
                    'version' => $import->source_version,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $nameRows[] = $this->nameRow(
                    import: $import,
                    source: $source,
                    taxonId: $taxonId,
                    sourceRecordId: $record->sourceRecordId,
                    name: $record->scientificName,
                    type: 'scientific',
                    language: null,
                    preferred: true,
                    now: $now,
                );

                if ($record->commonName !== null) {
                    $nameRows[] = $this->nameRow(
                        import: $import,
                        source: $source,
                        taxonId: $taxonId,
                        sourceRecordId: $record->sourceRecordId,
                        name: $record->commonName,
                        type: 'common',
                        language: $record->language,
                        preferred: true,
                        now: $now,
                    );
                }
            }

            Taxon::query()->upsert(
                $taxonRows,
                ['id'],
                [
                    'accepted_taxon_id',
                    'resolution_status',
                    'requires_review',
                    'is_active',
                    'updated_at',
                ],
            );
            TaxonVersion::query()->upsert(
                $versionRows,
                ['taxon_import_id', 'source_record_id'],
                array_values(array_diff(
                    array_keys($versionRows[0] ?? []),
                    ['taxon_import_id', 'source_record_id', 'created_at'],
                )),
            );
            TaxonExternalIdentifier::query()->upsert(
                $externalIdentifierRows,
                ['taxon_source_id', 'external_identifier', 'identifier_type'],
                ['taxon_id', 'version', 'is_active', 'updated_at'],
            );
            TaxonName::query()->upsert(
                $nameRows,
                ['taxon_import_id', 'import_key'],
                [
                    'taxon_id',
                    'taxon_source_id',
                    'locale',
                    'language',
                    'name',
                    'normalized_name',
                    'is_preferred',
                    'is_verified',
                    'is_active',
                    'updated_at',
                ],
            );

            if ($changeRows !== []) {
                TaxonChange::query()->insert($changeRows);
            }

            $this->storeIssues($import, $issues);

            return [
                'processed' => count($rows),
                'inserted' => $inserted,
                'updated' => $updated,
                'unchanged' => $unchanged,
                'synonyms' => $synonyms,
                'errors' => count($issues),
            ];
        });
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array{list<TaxonomyRecord>, list<array<string, mixed>>}
     */
    private function mapRecords(array $rows): array
    {
        $records = [];
        $issues = [];

        foreach ($rows as $row) {
            try {
                $records[] = TaxonomyRecord::fromSnapshot($row);
            } catch (InvalidArgumentException $exception) {
                $issues[] = [
                    'source_row' => (int) ($row['_source_row'] ?? 0),
                    'source_record_id' => $row['source_record_id'] ?? null,
                    'severity' => 'error',
                    'code' => $exception->getMessage(),
                    'context' => [
                        'scientific_name' => $row['scientific_name'] ?? null,
                    ],
                ];
            }
        }

        return [$records, $issues];
    }

    /**
     * @param  array<string, mixed>  $version
     */
    private function versionChanged(TaxonVersion $existing, array $version): bool
    {
        foreach ([
            'parent_taxon_id',
            'rank',
            'scientific_name',
            'canonical_name',
            'authorship',
            'nomenclatural_code',
            'taxonomic_status',
            'is_extinct',
            'is_marine',
            'is_freshwater',
            'is_terrestrial',
        ] as $attribute) {
            if ($existing->getAttribute($attribute) !== $version[$attribute]) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function nameRow(
        TaxonImport $import,
        TaxonSource $source,
        int $taxonId,
        string $sourceRecordId,
        string $name,
        string $type,
        ?string $language,
        bool $preferred,
        CarbonImmutable $now,
    ): array {
        $normalized = $this->identity->normalizeName($name);
        $locale = $language !== null && mb_strlen($language) <= 12
            ? mb_strtolower($language)
            : null;

        return [
            'taxon_id' => $taxonId,
            'taxon_import_id' => $import->id,
            'taxon_source_id' => $source->id,
            'locale' => $locale,
            'language' => $language,
            'name' => $name,
            'normalized_name' => $normalized,
            'name_type' => $type,
            'source_record_id' => $sourceRecordId,
            'import_key' => $this->identity->importedNameKey(
                $sourceRecordId,
                $type,
                $normalized,
                $locale,
            ),
            'is_preferred' => $preferred,
            'is_verified' => true,
            'is_local_override' => false,
            'is_active' => false,
            'metadata' => json_encode([], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $issues
     */
    private function storeIssues(TaxonImport $import, array $issues): void
    {
        if ($issues === []) {
            return;
        }

        $remaining = max(
            0,
            (int) config('taxonomy.max_stored_issues')
                - $import->issues()->count(),
        );
        $now = CarbonImmutable::now();

        TaxonImportIssue::query()->insert(array_map(
            static fn (array $issue): array => [
                'taxon_import_id' => $import->id,
                'source_row' => $issue['source_row'],
                'source_record_id' => $issue['source_record_id'],
                'severity' => $issue['severity'],
                'code' => $issue['code'],
                'context' => json_encode($issue['context'], JSON_THROW_ON_ERROR),
                'created_at' => $now,
            ],
            array_slice($issues, 0, $remaining),
        ));
    }
}
