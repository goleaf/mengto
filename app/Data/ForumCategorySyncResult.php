<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ForumCategorySyncResult
{
    /**
     * @param  list<string>  $archivedStableKeys
     */
    public function __construct(
        public int $rootCount,
        public int $subcategoryCount,
        public int $translationCount,
        public array $archivedStableKeys,
    ) {}
}
