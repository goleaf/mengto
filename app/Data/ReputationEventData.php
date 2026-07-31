<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class ReputationEventData
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public User $recipient,
        public string $dimension,
        public string $eventType,
        public string $sourceEntityType,
        public string $sourceEntityId,
        public int $amount,
        public string $reasonCode,
        public string $explanationTranslationKey,
        public string $idempotencyKey,
        public ?User $actor = null,
        public ?int $forumCategoryId = null,
        public ?int $taxonId = null,
        public ?string $locationScopeKey = null,
        public ?int $reversalOfEventId = null,
        public array $metadata = [],
        public ?CarbonImmutable $effectiveAt = null,
        public ?CarbonImmutable $expiresAt = null,
        public ?CarbonImmutable $reviewAt = null,
    ) {}
}
