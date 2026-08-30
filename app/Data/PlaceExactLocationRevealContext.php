<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\PlaceAccessPurpose;

final readonly class PlaceExactLocationRevealContext
{
    public function __construct(
        public ?PlaceAccessPurpose $purpose,
        public ?int $eventId,
        public string $channel,
    ) {}
}
