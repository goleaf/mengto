<?php

declare(strict_types=1);

namespace App\Data;

final readonly class SearchContactRelayData
{
    public function __construct(
        public string $purpose,
        public string $message,
        public string $idempotencyKey,
    ) {}

    /** @param array<string, mixed> $validated */
    public static function fromValidated(array $validated): self
    {
        return new self(
            purpose: (string) $validated['purpose'],
            message: trim((string) $validated['message']),
            idempotencyKey: (string) $validated['idempotency_key'],
        );
    }
}
