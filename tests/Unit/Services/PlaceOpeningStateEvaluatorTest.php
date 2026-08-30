<?php

declare(strict_types=1);

use App\Data\LocalOpeningInterval;
use App\Data\PlaceScheduleSnapshot;
use App\Data\ScheduleExceptionSnapshot;
use App\Enums\PlaceOpeningState;
use App\Enums\PlaceScheduleCoverage;
use App\Enums\PlaceScheduleExceptionKind;
use App\Enums\PlaceStatus;
use App\Enums\PlaceVerificationStatus;
use App\Services\PlaceOpeningStateEvaluator;
use Carbon\CarbonImmutable;

function emergencySchedule(
    array $weeklyIntervals = [],
    array $exceptions = [],
    string $timezone = 'Europe/Vilnius',
    PlaceScheduleCoverage $coverage = PlaceScheduleCoverage::Complete,
    PlaceVerificationStatus $verification = PlaceVerificationStatus::Verified,
    string $verifiedAt = '2026-08-01T00:00:00Z',
    string $freshUntil = '2026-12-31T00:00:00Z',
): PlaceScheduleSnapshot {
    return new PlaceScheduleSnapshot(
        timezone: $timezone,
        coverage: $coverage,
        verificationStatus: $verification,
        verificationSource: 'verified-clinic-hours',
        observedAt: CarbonImmutable::parse($verifiedAt),
        verifiedAt: CarbonImmutable::parse($verifiedAt),
        freshUntil: CarbonImmutable::parse($freshUntil),
        weeklyIntervals: $weeklyIntervals,
        exceptionsByDate: $exceptions,
    );
}

function emergencyInterval(
    int $startsAtMinute,
    int $endsAtMinute,
    bool $appointmentOnly = false,
): LocalOpeningInterval {
    return new LocalOpeningInterval($startsAtMinute, $endsAtMinute, $appointmentOnly);
}

function evaluateEmergencySchedule(
    string $reference,
    ?PlaceScheduleSnapshot $schedule,
    PlaceStatus $status = PlaceStatus::Active,
): App\Data\PlaceOpeningStateResult {
    return (new PlaceOpeningStateEvaluator)->evaluate(
        $status,
        $schedule,
        CarbonImmutable::parse($reference),
    );
}

test('missing and untrustworthy schedules never become optimistic opening states', function (
    string $variant,
    string $expected,
    string $reason,
): void {
    $schedule = match ($variant) {
        'missing' => null,
        'missing-timezone' => emergencySchedule(timezone: ''),
        'offset-timezone' => emergencySchedule(timezone: '+03:00'),
        'abbreviation-timezone' => emergencySchedule(timezone: 'EEST'),
        'unverified' => emergencySchedule(verification: PlaceVerificationStatus::NotAssessed),
        'expired' => emergencySchedule(verification: PlaceVerificationStatus::Expired),
        'freshness-boundary' => emergencySchedule(freshUntil: '2026-08-30T09:00:00Z'),
    };
    $result = evaluateEmergencySchedule('2026-08-30T09:00:00Z', $schedule);

    expect($result->state->value)->toBe($expected)
        ->and($result->reason)->toBe($reason)
        ->and($result->appointmentOnly)->toBeNull();
})->with([
    'missing schedule' => ['missing', 'status_unknown', 'missing_schedule'],
    'missing timezone' => ['missing-timezone', 'status_unknown', 'invalid_timezone'],
    'offset timezone' => ['offset-timezone', 'status_unknown', 'invalid_timezone'],
    'timezone abbreviation' => ['abbreviation-timezone', 'status_unknown', 'invalid_timezone'],
    'unverified schedule' => ['unverified', 'status_unknown', 'unverified_schedule'],
    'expired verification state' => ['expired', 'stale_schedule', 'stale_schedule'],
    'freshness boundary is exclusive' => ['freshness-boundary', 'stale_schedule', 'stale_schedule'],
]);

test('temporary closure wins over every schedule fact', function (): void {
    $schedule = emergencySchedule([
        7 => [emergencyInterval(0, 1440)],
    ]);

    $result = evaluateEmergencySchedule(
        '2026-08-30T09:00:00Z',
        $schedule,
        PlaceStatus::TemporarilyClosed,
    );

    expect($result->state)->toBe(PlaceOpeningState::TemporarilyClosed)
        ->and($result->reason)->toBe('place_temporarily_closed');
});

test('weekly intervals use half open boundaries and an elapsed opening soon threshold', function (
    string $reference,
    string $expected,
): void {
    $schedule = emergencySchedule([
        1 => [emergencyInterval(9 * 60, 17 * 60)],
    ]);

    expect(evaluateEmergencySchedule($reference, $schedule)->state->value)->toBe($expected);
})->with([
    'exact sixty minute threshold' => ['2026-08-31T05:00:00Z', 'opening_soon'],
    'exact opening boundary' => ['2026-08-31T06:00:00Z', 'open_now'],
    'inside interval' => ['2026-08-31T10:00:00Z', 'open_now'],
    'exact closing boundary' => ['2026-08-31T14:00:00Z', 'closed'],
]);

test('overnight intervals are anchored to the prior local weekday', function (
    string $reference,
    string $expected,
): void {
    $schedule = emergencySchedule([
        6 => [emergencyInterval(20 * 60, 29 * 60)],
    ]);

    expect(evaluateEmergencySchedule($reference, $schedule)->state->value)->toBe($expected);
})->with([
    'overnight start' => ['2026-08-29T17:00:00Z', 'open_now'],
    'overnight final second' => ['2026-08-30T01:59:59Z', 'open_now'],
    'overnight exact end' => ['2026-08-30T02:00:00Z', 'closed'],
]);

test('date exceptions replace weekly hours and suppress prior overnight carry in', function (): void {
    $closed = new ScheduleExceptionSnapshot(
        localDate: '2026-12-25',
        kind: PlaceScheduleExceptionKind::FullClosure,
        verificationStatus: PlaceVerificationStatus::Verified,
        verificationSource: 'holiday-hours',
        observedAt: CarbonImmutable::parse('2026-12-01T00:00:00Z'),
        verifiedAt: CarbonImmutable::parse('2026-12-01T00:00:00Z'),
        freshUntil: CarbonImmutable::parse('2026-12-26T00:00:00Z'),
        intervals: [],
    );
    $special = new ScheduleExceptionSnapshot(
        localDate: '2026-08-30',
        kind: PlaceScheduleExceptionKind::SpecialOpening,
        verificationStatus: PlaceVerificationStatus::Verified,
        verificationSource: 'special-hours',
        observedAt: CarbonImmutable::parse('2026-08-20T00:00:00Z'),
        verifiedAt: CarbonImmutable::parse('2026-08-20T00:00:00Z'),
        freshUntil: CarbonImmutable::parse('2026-08-31T00:00:00Z'),
        intervals: [emergencyInterval(18 * 60, 22 * 60)],
    );

    $holidaySchedule = emergencySchedule(
        weeklyIntervals: [4 => [emergencyInterval(20 * 60, 29 * 60)]],
        exceptions: ['2026-12-25' => $closed],
        freshUntil: '2027-01-01T00:00:00Z',
    );
    $specialSchedule = emergencySchedule(
        weeklyIntervals: [7 => [emergencyInterval(10 * 60, 16 * 60)]],
        exceptions: ['2026-08-30' => $special],
    );

    expect(evaluateEmergencySchedule('2026-12-24T22:30:00Z', $holidaySchedule)->state)
        ->toBe(PlaceOpeningState::Closed)
        ->and(evaluateEmergencySchedule('2026-08-30T09:00:00Z', $specialSchedule)->state)
        ->toBe(PlaceOpeningState::Closed)
        ->and(evaluateEmergencySchedule('2026-08-30T16:30:00Z', $specialSchedule)->state)
        ->toBe(PlaceOpeningState::OpenNow);
});

test('appointment only is a qualifier and never a walk in claim', function (): void {
    $schedule = emergencySchedule([
        6 => [emergencyInterval(10 * 60, 14 * 60, appointmentOnly: true)],
    ], freshUntil: '2027-01-01T00:00:00Z');

    $result = evaluateEmergencySchedule('2026-12-26T08:30:00Z', $schedule);

    expect($result->state)->toBe(PlaceOpeningState::OpenNow)
        ->and($result->appointmentOnly)->toBeTrue()
        ->and($result->reason)->toBe('appointment_only');
});

test('DST gap boundaries clamp forward and fold boundaries cover both repeated times', function (): void {
    $gapSchedule = emergencySchedule([
        7 => [emergencyInterval(3 * 60 + 30, 5 * 60)],
    ], verifiedAt: '2026-01-01T00:00:00Z', freshUntil: '2027-01-01T00:00:00Z');
    $foldSchedule = emergencySchedule([
        7 => [emergencyInterval(3 * 60 + 15, 3 * 60 + 45)],
    ], verifiedAt: '2026-01-01T00:00:00Z', freshUntil: '2027-01-01T00:00:00Z');

    expect(evaluateEmergencySchedule('2026-03-29T00:59:59Z', $gapSchedule)->state)
        ->toBe(PlaceOpeningState::OpeningSoon)
        ->and(evaluateEmergencySchedule('2026-03-29T01:00:00Z', $gapSchedule)->state)
        ->toBe(PlaceOpeningState::OpenNow)
        ->and(evaluateEmergencySchedule('2026-03-29T01:15:00Z', $gapSchedule)->state)
        ->toBe(PlaceOpeningState::OpenNow)
        ->and(evaluateEmergencySchedule('2026-10-25T00:30:00Z', $foldSchedule)->state)
        ->toBe(PlaceOpeningState::OpenNow)
        ->and(evaluateEmergencySchedule('2026-10-25T01:30:00Z', $foldSchedule)->state)
        ->toBe(PlaceOpeningState::OpenNow)
        ->and(evaluateEmergencySchedule('2026-10-25T01:45:00Z', $foldSchedule)->state)
        ->toBe(PlaceOpeningState::Closed);
});
