<?php

declare(strict_types=1);

namespace App\Data;

final readonly class TaxonomySnapshotChunk
{
    /**
     * @param  list<array<string, string|null>>  $rows
     */
    public function __construct(
        public array $rows,
        public int $nextOffset,
        public int $lastRow,
        public bool $isComplete,
    ) {}
}
