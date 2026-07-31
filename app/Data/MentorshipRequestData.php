<?php

declare(strict_types=1);

namespace App\Data;

final readonly class MentorshipRequestData
{
    public function __construct(
        public string $message,
        public string $language,
        public ?string $locationScope,
        public string $communicationPreference,
        public bool $safetyAcknowledged,
        public string $idempotencyKey,
    ) {}
}
