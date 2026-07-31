<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumTopicStatus;
use Carbon\CarbonImmutable;

final readonly class ForumTopicLifecycleSnapshot
{
    public function __construct(
        public ForumTopicStatus $status,
        public bool $isStale,
        public bool $showsNecropostWarning,
        public bool $archiveReviewDue,
        public bool $retentionReviewDue,
        public bool $canBump,
        public bool $allowsAuthorReopen,
        public bool $allowsAuthorArchive,
        public bool $allowsAuthorRemove,
        public bool $hasLegalHold,
        public int $lockVersion,
        public ?CarbonImmutable $nextBumpAt,
        public CarbonImmutable $referenceAt,
    ) {}
}
