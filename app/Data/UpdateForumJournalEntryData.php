<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumJournalEntryKind;
use Carbon\CarbonImmutable;

final readonly class UpdateForumJournalEntryData
{
    /**
     * @param  list<array{key: string, value: int|float|string}>  $measurements
     */
    public function __construct(
        public ForumJournalEntryKind $kind,
        public string $title,
        public string $body,
        public CarbonImmutable $occurredAt,
        public string $timezone,
        public array $measurements,
        public int $expectedVersion,
    ) {}
}
