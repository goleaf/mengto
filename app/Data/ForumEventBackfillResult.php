<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ForumEventBackfillResult
{
    public function __construct(
        public int $catalogCreated,
        public int $catalogUpdated,
        public int $groupActivitiesCreated,
        public int $groupActivitiesLinked,
        public int $reviewRequired,
    ) {}
}
