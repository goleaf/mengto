<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumEventStatus;
use App\Enums\ForumEventType;

final readonly class ForumEventTypeDefinition
{
    /**
     * @param  list<string>  $organizerKinds
     * @param  list<string>  $builderSections
     * @param  list<string>  $capabilities
     */
    public function __construct(
        public ForumEventType $type,
        public string $nameTranslationKey,
        public string $descriptionTranslationKey,
        public string $category,
        public int $schemaVersion,
        public array $organizerKinds,
        public string $participantModel,
        public string $petModel,
        public array $builderSections,
        public array $capabilities,
        public string $riskTier,
        public string $icon,
        public ForumEventStatus $defaultStatus,
        public string $factoryState,
        public string $seedScenario,
    ) {}

    public function supports(string $capability): bool
    {
        return in_array($capability, $this->capabilities, true);
    }

    public function requiresSafetyReview(): bool
    {
        return in_array($this->riskTier, ['high', 'controlled'], true);
    }
}
