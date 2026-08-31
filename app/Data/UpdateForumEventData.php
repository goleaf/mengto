<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumEventPetParticipation;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use Carbon\CarbonImmutable;

final readonly class UpdateForumEventData
{
    public function __construct(
        public string $title,
        public string $summary,
        public ForumEventType $type,
        public ForumEventVisibility $visibility,
        public ForumEventRegistrationPolicy $registrationPolicy,
        public ForumEventPetParticipation $petParticipationMode,
        public ?int $capacity,
        public bool $waitlistEnabled,
        public ?string $locationScope,
        public ?string $exactLocation,
        public ?string $attendanceRequirements,
        public ?string $accessibilityInformation,
        public string $animalWelfareRules,
        public string $emergencyContactPlan,
        public string $idempotencyKey,
        public ?CarbonImmutable $registrationOpensAt = null,
        public ?CarbonImmutable $registrationClosesAt = null,
    ) {}
}
