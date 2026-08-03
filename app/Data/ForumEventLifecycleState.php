<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\ForumEventOccurrence;
use App\Models\ForumEventVersion;

final readonly class ForumEventLifecycleState
{
    public function __construct(
        public ForumEventOccurrence $occurrence,
        public ForumEventVersion $version,
    ) {}
}
