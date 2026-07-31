<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;

final readonly class CreateForumExpertSessionData
{
    public function __construct(
        public int $expertProfileId,
        public string $professionalScope,
        public string $jurisdiction,
        public string $title,
        public string $summary,
        public string $locale,
        public string $timezone,
        public CarbonImmutable $questionOpensAt,
        public CarbonImmutable $questionClosesAt,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public string $idempotencyKey,
    ) {}
}
