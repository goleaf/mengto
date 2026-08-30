<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\PlaceScheduleExceptionKind;
use App\Enums\PlaceVerificationStatus;
use Carbon\CarbonImmutable;

final readonly class ScheduleExceptionSnapshot
{
    /** @param list<LocalOpeningInterval> $intervals */
    public function __construct(
        public string $localDate,
        public PlaceScheduleExceptionKind $kind,
        public PlaceVerificationStatus $verificationStatus,
        public string $verificationSource,
        public CarbonImmutable $observedAt,
        public CarbonImmutable $verifiedAt,
        public CarbonImmutable $freshUntil,
        public array $intervals,
    ) {}
}
