<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumGroupActivityFormat;
use Carbon\CarbonImmutable;

final readonly class CreateForumGroupActivityData
{
    public function __construct(
        public string $title,
        public string $summary,
        public ForumGroupActivityFormat $format,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public string $timezone,
        public ?string $locationScope,
        public ?string $onlineUrl,
        public ?int $capacity,
        public ?string $participationNotes,
        public string $idempotencyKey,
    ) {}
}
