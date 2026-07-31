<?php

declare(strict_types=1);

namespace App\Data;

final readonly class TaxonomySnapshotAnalysis
{
    /**
     * @param  array<string, int|null>  $columnMap
     * @param  list<string>  $headers
     */
    public function __construct(
        public string $disk,
        public string $path,
        public string $checksum,
        public string $delimiter,
        public array $headers,
        public array $columnMap,
        public int $rowCount,
        public int $warningCount,
    ) {}
}
