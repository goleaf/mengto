<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ForumTaxonomyBackfillResult
{
    public function __construct(
        public int $categoryAssignments,
        public int $topicTypeAssignments,
        public int $unmappedCategories,
        public int $unmappedTopicTypes,
    ) {}
}
