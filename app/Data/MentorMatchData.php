<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumMentorshipType;

final readonly class MentorMatchData
{
    /**
     * @param  list<string>  $reasonTranslationKeys
     * @param  list<string>  $languages
     * @param  list<string>  $communicationPreferences
     */
    public function __construct(
        public int $scopeId,
        public int $mentorUserId,
        public string $mentorName,
        public string $headline,
        public string $summary,
        public ForumMentorshipType $type,
        public array $languages,
        public array $communicationPreferences,
        public ?string $locationScope,
        public ?string $categoryName,
        public ?string $scientificName,
        public bool $professionallyVerified,
        public int $score,
        public array $reasonTranslationKeys,
    ) {}
}
