<?php

declare(strict_types=1);

namespace App\Data;

use App\ValueObjects\TaxonRank;
use InvalidArgumentException;

final readonly class TaxonomyRecord
{
    public function __construct(
        public string $sourceRecordId,
        public ?string $parentSourceRecordId,
        public ?string $acceptedSourceRecordId,
        public string $scientificName,
        public string $canonicalName,
        public ?string $authorship,
        public TaxonRank $rank,
        public string $taxonomicStatus,
        public ?string $nomenclaturalCode,
        public ?string $commonName,
        public ?string $language,
        public bool $isExtinct,
        public ?bool $isMarine,
        public ?bool $isFreshwater,
        public ?bool $isTerrestrial,
        public int $sourceRow,
    ) {}

    /**
     * @param  array<string, string|null>  $row
     */
    public static function fromSnapshot(array $row): self
    {
        $sourceRecordId = trim((string) ($row['source_record_id'] ?? ''));
        $scientificName = trim((string) ($row['scientific_name'] ?? ''));
        $rank = trim((string) ($row['rank'] ?? ''));
        $status = trim((string) ($row['taxonomic_status'] ?? ''));

        if ($sourceRecordId === '' || mb_strlen($sourceRecordId) > 190) {
            throw new InvalidArgumentException('invalid-source-record-id');
        }

        if ($scientificName === '' || mb_strlen($scientificName) > 500) {
            throw new InvalidArgumentException('invalid-scientific-name');
        }

        if ($rank === '') {
            throw new InvalidArgumentException('invalid-rank');
        }

        if ($status === '' || mb_strlen($status) > 80) {
            throw new InvalidArgumentException('invalid-taxonomic-status');
        }

        return new self(
            sourceRecordId: $sourceRecordId,
            parentSourceRecordId: self::nullable($row['parent_source_record_id'] ?? null, 190),
            acceptedSourceRecordId: self::nullable($row['accepted_source_record_id'] ?? null, 190),
            scientificName: $scientificName,
            canonicalName: self::nullable($row['canonical_name'] ?? null, 500) ?? $scientificName,
            authorship: self::nullable($row['authorship'] ?? null, 500),
            rank: TaxonRank::fromSource($rank),
            taxonomicStatus: mb_strtolower($status),
            nomenclaturalCode: self::nullable($row['nomenclatural_code'] ?? null, 80),
            commonName: self::nullable($row['common_name'] ?? null, 500),
            language: self::nullable($row['language'] ?? null, 80),
            isExtinct: self::boolean($row['is_extinct'] ?? null) ?? false,
            isMarine: self::boolean($row['is_marine'] ?? null),
            isFreshwater: self::boolean($row['is_freshwater'] ?? null),
            isTerrestrial: self::boolean($row['is_terrestrial'] ?? null),
            sourceRow: (int) ($row['_source_row'] ?? 0),
        );
    }

    private static function nullable(?string $value, int $maximum): ?string
    {
        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return null;
        }

        if (mb_strlen($trimmed) > $maximum) {
            throw new InvalidArgumentException('field-too-long');
        }

        return $trimmed;
    }

    private static function boolean(?string $value): ?bool
    {
        $normalized = mb_strtolower(trim((string) $value));

        return match ($normalized) {
            '', 'null', 'unknown' => null,
            '1', 'true', 'yes', 'y' => true,
            '0', 'false', 'no', 'n' => false,
            default => throw new InvalidArgumentException('invalid-boolean'),
        };
    }
}
