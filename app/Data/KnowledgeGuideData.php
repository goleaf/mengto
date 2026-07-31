<?php

declare(strict_types=1);

namespace App\Data;

final readonly class KnowledgeGuideData
{
    /**
     * @param  list<string>  $sources
     * @param  list<string>  $protectedSections
     */
    public function __construct(
        public string $title,
        public string $summary,
        public string $body,
        public string $category,
        public string $type,
        public string $difficulty,
        public ?string $audience,
        public string $language,
        public ?string $jurisdiction,
        public ?int $taxonId,
        public ?int $discussionTopicId,
        public array $sources,
        public array $protectedSections,
        public string $changeSummary,
        public int $expectedLockVersion,
    ) {}
}
