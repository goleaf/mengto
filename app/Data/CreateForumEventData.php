<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use Carbon\CarbonImmutable;

final readonly class CreateForumEventData
{
    /**
     * @param  list<int>  $taxonIds
     */
    public function __construct(
        public string $title,
        public string $summary,
        public ForumEventType $type,
        public ForumEventVisibility $visibility,
        public ForumEventFormat $format,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public string $timezone,
        public ?int $capacity,
        public ForumEventRegistrationPolicy $registrationPolicy,
        public bool $waitlistEnabled,
        public ?string $locationScope,
        public ?string $exactLocation,
        public ?string $onlineUrl,
        public ?string $attendanceRequirements,
        public ?string $vaccinationRequirements,
        public ?string $vaccinationJurisdiction,
        public ?int $minimumAnimalAgeMonths,
        public ?int $maximumAnimalAgeMonths,
        public ?string $accessibilityInformation,
        public int $costMinor,
        public string $currency,
        public ?string $refundPolicy,
        public ForumEventPhotoConsent $photoConsentMode,
        public string $animalWelfareRules,
        public string $emergencyContactPlan,
        public ?int $groupId,
        public array $taxonIds,
        public string $locale,
        public string $idempotencyKey,
    ) {}
}
