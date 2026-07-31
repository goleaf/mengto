<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumCommunityNoteType;

final readonly class CommunityNoteData
{
    /** @param list<array{url: string, label: string}> $evidence */
    public function __construct(
        public string $subjectType,
        public int $subjectId,
        public ForumCommunityNoteType $type,
        public string $body,
        public array $evidence,
        public ?string $jurisdiction,
        public ?string $speciesContext,
        public int $expectedLockVersion = 0,
    ) {}
}
