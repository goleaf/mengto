<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\PlaceScheduleCoverage;
use App\Enums\PlaceVerificationStatus;
use Carbon\CarbonImmutable;

final readonly class PlaceScheduleSnapshot
{
    /**
     * @param array<int, list<LocalOpeningInterval>> $weeklyIntervals
     * @param array<string, ScheduleExceptionSnapshot> $exceptionsByDate
     */
    public function __construct(
        public string $timezone,
        public PlaceScheduleCoverage $coverage,
        public PlaceVerificationStatus $verificationStatus,
        public string $verificationSource,
        public CarbonImmutable $observedAt,
        public CarbonImmutable $verifiedAt,
        public CarbonImmutable $freshUntil,
        public array $weeklyIntervals,
        public array $exceptionsByDate,
    ) {}
}
