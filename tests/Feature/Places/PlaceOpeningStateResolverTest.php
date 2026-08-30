<?php

declare(strict_types=1);

use App\Actions\ReplacePlaceSchedule;
use App\Data\Places\CanonicalPlaceFactData;
use App\Data\Places\ReplacePlaceScheduleData;
use App\Enums\PlaceFactVisibility;
use App\Enums\PlaceOpeningState;
use App\Enums\PlaceScheduleExceptionKind;
use App\Enums\PlaceScheduleMode;
use App\Enums\PlaceSubmissionSource;
use App\Enums\PlaceTemporaryClosureStatus;
use App\Enums\PlaceVerificationScope;
use App\Models\Place;
use App\Models\PlaceFact;
use App\Models\PlaceFactSelection;
use App\Models\PlaceOpeningInterval;
use App\Models\PlaceSchedule;
use App\Models\PlaceScheduleException;
use App\Models\PlaceScheduleExceptionInterval;
use App\Models\PlaceTemporaryClosure;
use App\Models\User;
use App\Services\PlaceOpeningStateResolver;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

test('missing timezone is unknown and appointment-only never implies an open walk-in state', function (): void {
    $place = Place::factory()->create();
    $resolver = app(PlaceOpeningStateResolver::class);

    expect($resolver->resolve($place, CarbonImmutable::parse('2026-08-31 09:00:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Unknown);

    canonicalScheduleFor($place, timezone: 'Europe/Vilnius', mode: PlaceScheduleMode::AppointmentOnly);

    expect($resolver->resolve($place->fresh(), CarbonImmutable::parse('2026-08-31 09:00:00 UTC'))->state)
        ->toBe(PlaceOpeningState::AppointmentOnly);
});

test('weekly intervals resolve open closed and opening soon from the place source timezone', function (): void {
    $place = Place::factory()->create();
    $schedule = canonicalScheduleFor($place, timezone: 'Europe/Vilnius');
    PlaceOpeningInterval::factory()->for($schedule, 'schedule')->create([
        'weekday' => 1,
        'opens_at' => '09:00',
        'closes_at' => '17:00',
        'spans_next_day' => false,
    ]);
    $resolver = app(PlaceOpeningStateResolver::class);

    expect($resolver->resolve($place, CarbonImmutable::parse('2026-08-31 05:00:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Closed)
        ->and($resolver->resolve($place, CarbonImmutable::parse('2026-08-31 05:30:00 UTC'))->state)
        ->toBe(PlaceOpeningState::OpeningSoon)
        ->and($resolver->resolve($place, CarbonImmutable::parse('2026-08-31 07:00:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Open)
        ->and($resolver->resolve($place, CarbonImmutable::parse('2026-08-31 15:00:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Closed);
});

test('overnight carry is open unless a date-specific closure replaces that whole local date', function (): void {
    $place = Place::factory()->create();
    $schedule = canonicalScheduleFor($place, timezone: 'Europe/Vilnius');
    PlaceOpeningInterval::factory()->for($schedule, 'schedule')->create([
        'weekday' => 1,
        'opens_at' => '20:00',
        'closes_at' => '02:00',
        'spans_next_day' => true,
    ]);
    $resolver = app(PlaceOpeningStateResolver::class);

    expect($resolver->resolve($place, CarbonImmutable::parse('2026-08-31 22:30:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Open);

    PlaceScheduleException::factory()->for($schedule, 'schedule')->for(
        canonicalPlaceFact($place, 'schedule-exception:2026-09-01'),
        'fact',
    )->create([
        'local_date' => '2026-09-01',
        'kind' => PlaceScheduleExceptionKind::Closed,
    ]);

    expect($resolver->resolve($place->fresh(), CarbonImmutable::parse('2026-08-31 22:30:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Closed);
});

test('date-specific opening overrides weekly hours and a temporary closure has higher precedence', function (): void {
    $place = Place::factory()->create();
    $schedule = canonicalScheduleFor($place, timezone: 'Europe/Vilnius');
    $exception = PlaceScheduleException::factory()->for($schedule, 'schedule')->for(
        canonicalPlaceFact($place, 'schedule-exception:2026-09-02'),
        'fact',
    )->create([
        'local_date' => '2026-09-02',
        'kind' => PlaceScheduleExceptionKind::SpecialOpening,
    ]);
    PlaceScheduleExceptionInterval::factory()->for($exception, 'exception')->create([
        'opens_at' => '10:00',
        'closes_at' => '12:00',
        'spans_next_day' => false,
    ]);
    $at = CarbonImmutable::parse('2026-09-02 08:30:00 UTC');
    $resolver = app(PlaceOpeningStateResolver::class);

    expect($resolver->resolve($place, $at)->state)->toBe(PlaceOpeningState::Open);

    PlaceTemporaryClosure::factory()->for($place)->for(
        canonicalPlaceFact($place, 'temporary-closure:maintenance'),
        'fact',
    )->create([
        'starts_at' => $at->subHour(),
        'ends_at' => $at->addHour(),
        'timezone' => 'Europe/Vilnius',
        'status' => PlaceTemporaryClosureStatus::Active,
        'reason_code' => 'maintenance',
    ]);

    expect($resolver->resolve($place->fresh(), $at)->state)
        ->toBe(PlaceOpeningState::TemporarilyClosed);
});

test('a stale schedule remains explicitly stale instead of becoming confirmed closed or open', function (): void {
    $place = Place::factory()->create();
    $schedule = canonicalScheduleFor(
        $place,
        timezone: 'Europe/Vilnius',
        freshUntil: CarbonImmutable::parse('2026-08-31 07:59:59 UTC'),
    );
    PlaceOpeningInterval::factory()->for($schedule, 'schedule')->create([
        'weekday' => 1,
        'opens_at' => '00:00',
        'closes_at' => '23:59',
        'spans_next_day' => false,
    ]);

    expect(app(PlaceOpeningStateResolver::class)
        ->resolve($place, CarbonImmutable::parse('2026-08-31 08:00:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Stale);
});

test('DST spring gaps clamp opening boundaries and fall folds widen closing boundaries deterministically', function (): void {
    $springPlace = Place::factory()->create();
    $spring = canonicalScheduleFor($springPlace, timezone: 'Europe/Vilnius');
    PlaceOpeningInterval::factory()->for($spring, 'schedule')->create([
        'weekday' => 7,
        'opens_at' => '03:30',
        'closes_at' => '05:00',
        'spans_next_day' => false,
    ]);
    $resolver = app(PlaceOpeningStateResolver::class);

    expect($resolver->resolve($springPlace, CarbonImmutable::parse('2026-03-29 00:30:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Closed)
        ->and($resolver->resolve($springPlace, CarbonImmutable::parse('2026-03-29 01:00:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Open);

    $foldPlace = Place::factory()->create();
    $fold = canonicalScheduleFor($foldPlace, timezone: 'Europe/Vilnius');
    PlaceOpeningInterval::factory()->for($fold, 'schedule')->create([
        'weekday' => 7,
        'opens_at' => '03:30',
        'closes_at' => '03:45',
        'spans_next_day' => false,
    ]);

    expect($resolver->resolve($foldPlace, CarbonImmutable::parse('2026-10-25 00:30:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Open)
        ->and($resolver->resolve($foldPlace, CarbonImmutable::parse('2026-10-25 01:30:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Open)
        ->and($resolver->resolve($foldPlace, CarbonImmutable::parse('2026-10-25 01:45:00 UTC'))->state)
        ->toBe(PlaceOpeningState::Closed);
});

test('schedule replacement rejects contradictory overlapping intervals before mutation', function (): void {
    $manager = User::factory()->create();
    $place = Place::factory()->for($manager, 'owner')->create(['lock_version' => 0]);

    expect(fn () => app(ReplacePlaceSchedule::class)->handle(
        actor: $manager,
        place: $place,
        data: new ReplacePlaceScheduleData(
            timezone: 'Europe/Vilnius',
            mode: PlaceScheduleMode::Regular,
            intervals: [
                ['weekday' => 1, 'opens_at' => '09:00', 'closes_at' => '12:00', 'spans_next_day' => false, 'is_all_day' => false],
                ['weekday' => 1, 'opens_at' => '11:00', 'closes_at' => '13:00', 'spans_next_day' => false, 'is_all_day' => false],
            ],
            exceptions: [],
            expectedPlaceVersion: 0,
            expectedSelectionVersion: null,
            idempotencyKey: 'contradictory-schedule-create-0001',
            fact: canonicalFactData(),
        ),
    ))->toThrow(ValidationException::class)
        ->and(PlaceSchedule::query()->count())->toBe(0);
});

function canonicalScheduleFor(
    Place $place,
    string $timezone,
    PlaceScheduleMode $mode = PlaceScheduleMode::Regular,
    ?CarbonImmutable $freshUntil = null,
): PlaceSchedule {
    $fact = canonicalPlaceFact($place, 'schedule', $freshUntil);

    $schedule = PlaceSchedule::factory()->for($place)->for($fact, 'fact')->create([
        'timezone' => $timezone,
        'mode' => $mode,
    ]);
    PlaceFactSelection::factory()->for($place)->for($fact, 'currentFact')->create([
        'field_slot' => 'schedule',
        'lock_version' => 0,
    ]);

    return $schedule;
}

function canonicalPlaceFact(
    Place $place,
    string $slot,
    ?CarbonImmutable $freshUntil = null,
): PlaceFact {
    return PlaceFact::factory()->canonicalFor($place, $slot)->create([
        'fresh_until' => $freshUntil ?? CarbonImmutable::parse('2027-01-01 00:00:00 UTC'),
    ]);
}

function canonicalFactData(): CanonicalPlaceFactData
{
    return new CanonicalPlaceFactData(
        sourceKind: PlaceSubmissionSource::Organization,
        verificationScope: PlaceVerificationScope::ManagerConfirmed,
        visibility: PlaceFactVisibility::Public,
        sourceReference: 'Manager-provided schedule record.',
        observedAt: CarbonImmutable::parse('2026-08-30 09:00:00 UTC'),
        verifiedAt: CarbonImmutable::parse('2026-08-30 10:00:00 UTC'),
        freshUntil: CarbonImmutable::parse('2026-09-29 10:00:00 UTC'),
        expiresAt: null,
        evidenceFactIds: [],
    );
}
