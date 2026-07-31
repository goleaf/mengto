<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumMentorProfileState;

final readonly class MentorProfileData
{
    /**
     * @param  list<string>  $languages
     * @param  list<string>  $communicationPreferences
     * @param  array<string, mixed>  $availability
     */
    public function __construct(
        public ForumMentorProfileState $state,
        public string $headline,
        public string $summary,
        public array $languages,
        public ?string $locationScope,
        public string $timezone,
        public array $communicationPreferences,
        public array $availability,
        public int $capacity,
        public bool $isPublic,
        public bool $safetyAcknowledged,
        public int $expectedLockVersion = 0,
    ) {}
}
