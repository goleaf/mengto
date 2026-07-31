<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumMentorshipType;

final readonly class MentorScopeData
{
    public function __construct(
        public ForumMentorshipType $type,
        public string $experienceSummary,
        public ?int $forumCategoryId,
        public ?int $taxonId,
        public bool $requiresVerifiedExpertise,
        public bool $isActive = true,
    ) {}
}
