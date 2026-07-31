<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumPollEligibility;
use App\Enums\ForumPollResultVisibility;
use App\Enums\ForumPollType;
use App\Enums\ForumPollVoterVisibility;
use Carbon\CarbonImmutable;

final readonly class CreateForumPollData
{
    /**
     * @param  list<string>  $options
     */
    public function __construct(
        public string $question,
        public ?string $description,
        public array $options,
        public ForumPollType $type,
        public ForumPollVoterVisibility $voterVisibility,
        public ForumPollResultVisibility $resultVisibility,
        public bool $isVoteEditable,
        public ForumPollEligibility $eligibility,
        public ?CarbonImmutable $closesAt,
        public string $idempotencyKey,
    ) {}
}
