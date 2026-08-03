<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ForumEventLifecycleBackfillResult
{
    public function __construct(
        public int $eventsInitialized,
        public int $registrationsUpdated,
        public int $petLinksCreated,
    ) {}
}
