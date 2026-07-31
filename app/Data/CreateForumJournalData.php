<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumJournalType;
use App\Enums\ForumVisibility;
use Carbon\CarbonImmutable;

final readonly class CreateForumJournalData
{
    public function __construct(
        public string $title,
        public string $body,
        public string $categoryKey,
        public ForumJournalType $type,
        public ForumVisibility $visibility,
        public CarbonImmutable $startedOn,
        public string $timezone,
        public string $locale,
        public string $idempotencyKey,
    ) {}
}
