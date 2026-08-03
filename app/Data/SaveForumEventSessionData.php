<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumEventSessionReservationPolicy;
use App\Enums\ForumEventSessionRole;
use App\Enums\ForumEventSessionStatus;
use App\Enums\ForumEventSessionType;
use Carbon\CarbonImmutable;

final readonly class SaveForumEventSessionData
{
    /**
     * @param  list<array{user_id: int, role: ForumEventSessionRole, is_public: bool}>  $staff
     */
    public function __construct(
        public int $occurrenceId,
        public ?int $trackId,
        public ?int $roomId,
        public string $title,
        public ?string $summary,
        public ForumEventSessionType $type,
        public ForumEventSessionStatus $status,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public string $timezone,
        public ?int $capacity,
        public ForumEventSessionReservationPolicy $reservationPolicy,
        public bool $isRequired,
        public int $position,
        public array $staff,
        public ?string $conflictOverrideReason,
        public string $idempotencyKey,
    ) {}
}
