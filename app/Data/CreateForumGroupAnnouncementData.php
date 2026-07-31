<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;

final readonly class CreateForumGroupAnnouncementData
{
    public function __construct(
        public string $title,
        public string $body,
        public CarbonImmutable $publishedAt,
        public ?CarbonImmutable $expiresAt,
        public string $idempotencyKey,
    ) {}
}
