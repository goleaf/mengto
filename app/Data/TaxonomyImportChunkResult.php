<?php

declare(strict_types=1);

namespace App\Data;

final readonly class TaxonomyImportChunkResult
{
    public function __construct(
        public int $processed,
        public int $inserted,
        public int $updated,
        public int $unchanged,
        public int $errors,
        public bool $isComplete,
    ) {}
}
