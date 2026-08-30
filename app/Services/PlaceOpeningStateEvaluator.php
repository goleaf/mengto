<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\LocalOpeningInterval;
use App\Data\PlaceOpeningStateResult;
use App\Data\PlaceScheduleSnapshot;
use App\Data\ScheduleExceptionSnapshot;
use App\Enums\PlaceOpeningState;
use App\Enums\PlaceScheduleCoverage;
use App\Enums\PlaceScheduleExceptionKind;
use App\Enums\PlaceStatus;
use App\Enums\PlaceVerificationStatus;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeZone;

final class PlaceOpeningStateEvaluator
{
    private const int OPENING_SOON_SECONDS = 3600;

    public function evaluate(
        PlaceStatus $placeStatus,
        ?PlaceScheduleSnapshot $schedule,
        CarbonImmutable $referenceInstant,
    ): PlaceOpeningStateResult {
        $reference = $referenceInstant->utc();

        if ($placeStatus === PlaceStatus::TemporarilyClosed) {
            return $this->result(
                PlaceOpeningState::TemporarilyClosed,
                $reference,
                reason: 'place_temporarily_closed',
            );
        }

        if ($schedule === null) {
            return $this->result(
                PlaceOpeningState::StatusUnknown,
                $reference,
                reason: 'missing_schedule',
            );
        }

        if (! $this->validTimezone($schedule->timezone)) {
            return $this->result(
                PlaceOpeningState::StatusUnknown,
                $reference,
                schedule: $schedule,
                reason: 'invalid_timezone',
            );
        }

        $evidenceState = $this->evidenceState(
            $schedule->verificationStatus,
            $schedule->verificationSource,
            $schedule->observedAt,
            $schedule->verifiedAt,
            $schedule->freshUntil,
            $reference,
        );

        if ($evidenceState !== null) {
            return $this->result(
                $evidenceState,
                $reference,
                schedule: $schedule,
                reason: $evidenceState === PlaceOpeningState::StaleSchedule
                    ? 'stale_schedule'
                    : 'unverified_schedule',
            );
        }

        if ($schedule->coverage !== PlaceScheduleCoverage::Complete
            || ! $this->structurallyValid($schedule)) {
            return $this->result(
                PlaceOpeningState::StatusUnknown,
                $reference,
                schedule: $schedule,
                reason: $schedule->coverage === PlaceScheduleCoverage::Complete
                    ? 'invalid_schedule'
                    : 'incomplete_schedule',
            );
        }

        $timezone = new DateTimeZone($schedule->timezone);
        $localDate = $reference->setTimezone($timezone)->startOfDay();
        $currentException = $schedule->exceptionsByDate[$localDate->toDateString()] ?? null;

        if ($currentException !== null) {
            $exceptionState = $this->exceptionEvidenceState($currentException, $reference);

            if ($exceptionState !== null) {
                return $this->result(
                    $exceptionState,
                    $reference,
                    schedule: $schedule,
                    reason: $exceptionState === PlaceOpeningState::StaleSchedule
                        ? 'stale_exception'
                        : 'unverified_exception',
                );
            }

            if ($currentException->kind === PlaceScheduleExceptionKind::FullClosure) {
                return $this->result(
                    PlaceOpeningState::Closed,
                    $reference,
                    schedule: $schedule,
                    reason: 'date_exception_closed',
                );
            }
        }

        $effective = $this->effectiveIntervals($schedule, $localDate, $timezone, $reference);

        if ($effective === null) {
            return $this->result(
                PlaceOpeningState::StatusUnknown,
                $reference,
                schedule: $schedule,
                reason: 'invalid_schedule',
            );
        }

        foreach ($effective as $interval) {
            if ($reference->greaterThanOrEqualTo($interval['opens_at'])
                && $reference->lessThan($interval['closes_at'])) {
                return $this->result(
                    PlaceOpeningState::OpenNow,
                    $reference,
                    appointmentOnly: $interval['appointment_only'],
                    transition: $interval['closes_at'],
                    schedule: $schedule,
                    reason: $interval['appointment_only'] ? 'appointment_only' : 'scheduled_open',
                );
            }
        }

        foreach ($effective as $interval) {
            if ($interval['opens_at']->lessThanOrEqualTo($reference)) {
                continue;
            }

            $secondsUntilOpening = $reference->diffInSeconds($interval['opens_at']);

            if ($secondsUntilOpening <= self::OPENING_SOON_SECONDS) {
                return $this->result(
                    PlaceOpeningState::OpeningSoon,
                    $reference,
                    appointmentOnly: $interval['appointment_only'],
                    transition: $interval['opens_at'],
                    schedule: $schedule,
                    reason: $interval['appointment_only']
                        ? 'appointment_only_opening_soon'
                        : 'scheduled_opening_soon',
                );
            }

            break;
        }

        return $this->result(
            PlaceOpeningState::Closed,
            $reference,
            schedule: $schedule,
            reason: 'scheduled_closed',
        );
    }

    private function validTimezone(string $timezone): bool
    {
        return $timezone === 'UTC'
            || in_array($timezone, DateTimeZone::listIdentifiers(), true);
    }

    private function evidenceState(
        PlaceVerificationStatus $status,
        string $source,
        CarbonImmutable $observedAt,
        CarbonImmutable $verifiedAt,
        CarbonImmutable $freshUntil,
        CarbonImmutable $reference,
    ): ?PlaceOpeningState {
        if ($status === PlaceVerificationStatus::Expired
            || $reference->greaterThanOrEqualTo($freshUntil)) {
            return PlaceOpeningState::StaleSchedule;
        }

        if (! in_array($status, [
            PlaceVerificationStatus::VenueConfirmed,
            PlaceVerificationStatus::OrganizationConfirmed,
            PlaceVerificationStatus::Verified,
        ], true)
            || trim($source) === ''
            || $observedAt->greaterThan($verifiedAt)
            || $verifiedAt->greaterThan($freshUntil)
            || $verifiedAt->greaterThan($reference)) {
            return PlaceOpeningState::StatusUnknown;
        }

        return null;
    }

    private function exceptionEvidenceState(
        ScheduleExceptionSnapshot $exception,
        CarbonImmutable $reference,
    ): ?PlaceOpeningState {
        return $this->evidenceState(
            $exception->verificationStatus,
            $exception->verificationSource,
            $exception->observedAt,
            $exception->verifiedAt,
            $exception->freshUntil,
            $reference,
        );
    }

    private function structurallyValid(PlaceScheduleSnapshot $schedule): bool
    {
        foreach ($schedule->weeklyIntervals as $weekday => $intervals) {
            if (! is_int($weekday) || $weekday < 1 || $weekday > 7
                || ! $this->validIntervals($intervals)) {
                return false;
            }
        }

        foreach ($schedule->exceptionsByDate as $localDate => $exception) {
            if ($localDate !== $exception->localDate
                || CarbonImmutable::createFromFormat('!Y-m-d', $localDate, 'UTC') === false
                || ($exception->kind === PlaceScheduleExceptionKind::FullClosure
                    && $exception->intervals !== [])
                || ($exception->kind === PlaceScheduleExceptionKind::SpecialOpening
                    && ($exception->intervals === [] || ! $this->validIntervals($exception->intervals)))) {
                return false;
            }
        }

        return true;
    }

    /** @param list<LocalOpeningInterval> $intervals */
    private function validIntervals(array $intervals): bool
    {
        $ordered = $intervals;
        usort($ordered, static fn (LocalOpeningInterval $left, LocalOpeningInterval $right): int => [
            $left->startsAtMinute,
            $left->endsAtMinute,
        ] <=> [
            $right->startsAtMinute,
            $right->endsAtMinute,
        ]);
        $previousEnd = null;

        foreach ($ordered as $interval) {
            if ($interval->startsAtMinute < 0
                || $interval->startsAtMinute > 1439
                || $interval->endsAtMinute <= $interval->startsAtMinute
                || $interval->endsAtMinute - $interval->startsAtMinute > 1440
                || $interval->endsAtMinute > 2879
                || ($previousEnd !== null && $interval->startsAtMinute < $previousEnd)) {
                return false;
            }

            $previousEnd = $interval->endsAtMinute;
        }

        return true;
    }

    /**
     * @return list<array{opens_at: CarbonImmutable, closes_at: CarbonImmutable, appointment_only: bool}>|null
     */
    private function effectiveIntervals(
        PlaceScheduleSnapshot $schedule,
        CarbonImmutable $localDate,
        DateTimeZone $timezone,
        CarbonImmutable $reference,
    ): ?array {
        $resolved = [];

        for ($offset = -1; $offset <= 1; $offset++) {
            $anchorDate = $localDate->addDays($offset);
            $dateKey = $anchorDate->toDateString();
            $exception = $schedule->exceptionsByDate[$dateKey] ?? null;

            if ($exception !== null) {
                $exceptionState = $this->exceptionEvidenceState($exception, $reference);

                if ($exceptionState !== null) {
                    if ($offset === 0) {
                        return null;
                    }

                    continue;
                }

                $intervals = $exception->kind === PlaceScheduleExceptionKind::SpecialOpening
                    ? $exception->intervals
                    : [];
            } else {
                $intervals = $schedule->weeklyIntervals[$anchorDate->isoWeekday()] ?? [];
            }

            foreach ($intervals as $interval) {
                $effectiveEnd = $interval->endsAtMinute;
                $nextDateHasException = isset(
                    $schedule->exceptionsByDate[$anchorDate->addDay()->toDateString()],
                );

                if ($effectiveEnd > 1440 && $nextDateHasException) {
                    $effectiveEnd = 1440;
                }

                $opensAt = $this->resolveBoundary(
                    $anchorDate,
                    $interval->startsAtMinute,
                    $timezone,
                    opening: true,
                );
                $closesAt = $this->resolveBoundary(
                    $anchorDate,
                    $effectiveEnd,
                    $timezone,
                    opening: false,
                );

                if ($opensAt === null || $closesAt === null || ! $opensAt->lessThan($closesAt)) {
                    return null;
                }

                $resolved[] = [
                    'opens_at' => $opensAt,
                    'closes_at' => $closesAt,
                    'appointment_only' => $interval->appointmentOnly,
                ];
            }
        }

        usort($resolved, static fn (array $left, array $right): int => [
            $left['opens_at']->getTimestamp(),
            $left['closes_at']->getTimestamp(),
            $left['appointment_only'],
        ] <=> [
            $right['opens_at']->getTimestamp(),
            $right['closes_at']->getTimestamp(),
            $right['appointment_only'],
        ]);

        for ($index = 1, $count = count($resolved); $index < $count; $index++) {
            if ($resolved[$index]['opens_at']->lessThan($resolved[$index - 1]['closes_at'])) {
                return null;
            }
        }

        return $resolved;
    }

    private function resolveBoundary(
        CarbonImmutable $anchorDate,
        int $minute,
        DateTimeZone $timezone,
        bool $opening,
    ): ?CarbonImmutable {
        $date = $anchorDate->addDays(intdiv($minute, 1440));
        $minuteOfDay = $minute % 1440;
        $wall = sprintf(
            '%s %02d:%02d:00',
            $date->toDateString(),
            intdiv($minuteOfDay, 60),
            $minuteOfDay % 60,
        );
        $naive = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $wall, new DateTimeZone('UTC'));

        if ($naive === false) {
            return null;
        }

        $transitions = $timezone->getTransitions(
            $naive->getTimestamp() - 172800,
            $naive->getTimestamp() + 172800,
        );
        $offsets = array_values(array_unique(array_map(
            static fn (array $transition): int => (int) $transition['offset'],
            $transitions,
        )));
        $candidates = [];

        foreach ($offsets as $offset) {
            $timestamp = $naive->getTimestamp() - $offset;
            $candidate = (new DateTimeImmutable('@'.$timestamp))->setTimezone($timezone);

            if ($candidate->format('Y-m-d H:i:s') === $wall) {
                $candidates[] = $timestamp;
            }
        }

        $candidates = array_values(array_unique($candidates));

        if ($candidates === []) {
            $previousOffset = null;

            foreach ($transitions as $transition) {
                $nextOffset = (int) $transition['offset'];

                if ($previousOffset !== null && $nextOffset > $previousOffset) {
                    $gapStartsAtWall = (int) $transition['ts'] + $previousOffset;
                    $gapEndsAtWall = (int) $transition['ts'] + $nextOffset;

                    if ($naive->getTimestamp() >= $gapStartsAtWall
                        && $naive->getTimestamp() < $gapEndsAtWall) {
                        return CarbonImmutable::createFromTimestampUTC((int) $transition['ts']);
                    }
                }

                $previousOffset = $nextOffset;
            }

            return null;
        }

        sort($candidates);
        $timestamp = $opening ? $candidates[0] : $candidates[array_key_last($candidates)];

        return CarbonImmutable::createFromTimestampUTC($timestamp);
    }

    private function result(
        PlaceOpeningState $state,
        CarbonImmutable $reference,
        ?bool $appointmentOnly = null,
        ?CarbonImmutable $transition = null,
        ?PlaceScheduleSnapshot $schedule = null,
        string $reason = 'unknown',
    ): PlaceOpeningStateResult {
        return new PlaceOpeningStateResult(
            state: $state,
            appointmentOnly: $appointmentOnly,
            evaluatedAt: $reference,
            nextTransitionAt: $transition,
            freshUntil: $schedule?->freshUntil,
            timezone: $schedule?->timezone,
            reason: $reason,
        );
    }
}
